<?php

namespace Eccube\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Common\Constant;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\ShipmentItem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\TableHelper;

class MembershipBillingCheckCommand extends \Knp\Command\Command
{

    protected $app;

    protected function configure() {
        $this
            ->setName('membershipbillingcheck')
            ->setDescription('Check For Membership Billing Status');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->app = $this->getSilexApplication();
        $this->app->initialize();
        $this->app->boot();
        $console = new Application();
        $logfile_path = $this->app['config']['root_dir'].'/app/log/MembershipBillingCheck.log';
        $this->app['orm.em']->getConnection()->getConfiguration()->setSQLLogger(null);
        $termInfos = $this->app['eccube.repository.master.term_info']->createQueryBuilder('t')
                ->andWhere("t.term_end >= '" . date('Y-m-d') . "'")
                ->andWhere('t.del_flg = 0')
                ->addOrderBy('t.term_year', 'desc')
                ->getQuery()
                ->getResult();
        if (is_null($termInfos)) {
            return;
        } else if(count($termInfos) < 1) {
            return;
        }
        $termInfo = $termInfos[0];
        $MembershipBillingStatusRep = $this->app['eccube.repository.membership_billing_status'];
        $targetCount = $MembershipBillingStatusRep->countTargetOrder($this->app['config']['membership_billing_target_status']);
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "実績未登録年会費支払受注 " . $targetCount . "件 検出\n", FILE_APPEND);
            $MembershipBillingStatusRep->insertFromOrder($this->app['config']['membership_billing_target_status']);
        }
        $targetCount = $MembershipBillingStatusRep->countNotPaymentStatus($this->app['config']['membership_billing_target_status']);
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費支払受注なし実績登録 " . $targetCount . "件 検出\n", FILE_APPEND);
            $MembershipBillingStatusRep->deleteFromOrder($this->app['config']['membership_billing_target_status']);
        }
        $targetCount = $MembershipBillingStatusRep->countRealFormerMenber($termInfo->getTermYear());
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前々年度年会費未払い休眠者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealFormerMember($termInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countRealDormantMenber($termInfo->getTermYear());
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前年度年会費未払い滞納者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealDormantMember($termInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countRealDelinquentMenber($termInfo->getTermYear(), array(6));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費未払い正会員 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealDelinquentMember($termInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($termInfo->getTermYear() - 2, array(7));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前々年度年会費支払済み元会員 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($termInfo->getTermYear() - 2, array(7), 5);
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($termInfo->getTermYear() - 1, array(5));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前年度年会費支払済み休眠者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($termInfo->getTermYear() - 1, array(5), 6);
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($termInfo->getTermYear(), array(6));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費支払済み滞納者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($termInfo->getTermYear(), array(6), 1);
        }
    }
}
