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

class EndOfTermCommand extends \Knp\Command\Command
{

    protected $app;

    protected function configure() {
        $this
            ->setName('endofterm')
            ->setDescription('End Term And Change New Term');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->app = $this->getSilexApplication();
        $this->app->initialize();
        $this->app->boot();
        $console = new Application();
        $logfile_path = $this->app['config']['root_dir'].'/app/log/EndOfTermCommand.log';
        $this->app['orm.em']->getConnection()->getConfiguration()->setSQLLogger(null);
        $termInfos = $this->app['eccube.repository.master.term_info']->createQueryBuilder('t')
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
        $newTermInfo = null;
        $this->getHelper('em')->getEntityManager()->getFilters()->disable('soft_delete');
        $newTermInfos = $this->app['eccube.repository.master.term_info']->createQueryBuilder('t')
                ->andWhere("t.term_year >= " . $termInfo->getTermYear())
                ->andWhere("t.term_start > '" . $termInfo->getTermStart()->format('Y-m-d H:i:s') . "'")
                ->andWhere("t.term_end > '" . date('Y-m-d') . "'")
                ->addOrderBy('t.term_year', 'desc')
                ->getQuery()
                ->getResult();
        $this->getHelper('em')->getEntityManager()->getFilters()->enable('soft_delete');
        if ($newTermInfos) {
            $newTermInfo = $newTermInfos[0];
        }
        if (is_null($newTermInfo)) {
            $insert_Date = date('Y-m-d');
            $newTermInfo = new \Eccube\Entity\Master\TermInfo();
            $newTermInfo->setTermName(date('Y年度', strtotime($insert_Date)));
            $newTermInfo->setTermStart(new \DateTime(date('Y-m-d 00:00:00', strtotime($insert_Date))));
            $newTermInfo->setTermEnd(new \DateTime(date('Y-m-d 23:59:59', strtotime('-1 day', strtotime('+1 year', strtotime($insert_Date))))));
            $newTermInfo->setTermYear(date('Y', strtotime($insert_Date)));
            $newTermInfo->setJapaneseYear($termInfo->getJapaneseYear() + ($newTermInfo->getTermYear() - $termInfo->getTermYear()));
            $newTermInfo->setDelFlg(1);
            $this->app['orm.em']->persist($newTermInfo);
            $this->app['orm.em']->flush();
        }
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年度切り替え " . $termInfo->getTermName() . "(" .  $termInfo->getTermYear() . ") => " . $newTermInfo->getTermName() . "(" .  $newTermInfo->getTermYear() . ")\n", FILE_APPEND);
        $this->getHelper('em')->getEntityManager()->getConnection()->executeQuery("UPDATE mtb_term_info SET del_flg = 1;");
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年度切り替えにつき年度情報無効化\n", FILE_APPEND);
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
        $targetCount = $MembershipBillingStatusRep->countRealFormerMenber($newTermInfo->getTermYear());
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前々年度年会費未払い休眠者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealFormerMember($newTermInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countRealDormantMenber($newTermInfo->getTermYear());
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前年度年会費未払い滞納者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealDormantMember($newTermInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countRealDelinquentMenber($newTermInfo->getTermYear(), array(6));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費未払い正会員 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updateRealDelinquentMember($newTermInfo->getTermYear());
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($newTermInfo->getTermYear() - 2, array(7));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前々年度年会費支払済み元会員 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($newTermInfo->getTermYear() - 2, array(7), 5);
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($newTermInfo->getTermYear() - 1, array(5));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "前年度年会費支払済み休眠者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($newTermInfo->getTermYear() - 1, array(5), 6);
        }
        $targetCount = $MembershipBillingStatusRep->countPaymentMember($newTermInfo->getTermYear(), array(6));
        if (0 < $targetCount) {
            file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費支払済み滞納者 " . $targetCount . "件 検出\n", FILE_APPEND);
            $result = $MembershipBillingStatusRep->updatePaymentMember($newTermInfo->getTermYear(), array(6), 1);
        }
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "年会費免除情報クリア\n", FILE_APPEND);
        $this->getHelper('em')->getEntityManager()->getConnection()->executeQuery("UPDATE dtb_customer_basic_info LEFT JOIN dtb_customer ON dtb_customer.customer_id = dtb_customer_basic_info.customer_id SET dtb_customer_basic_info.membership_exemption = 1 WHERE dtb_customer_basic_info.membership_exemption <> 1 AND dtb_customer.del_flg = 0;");
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . "次年度情報復元\n", FILE_APPEND);
        $newTermInfo->setDelFlg(0);
        $this->app['orm.em']->persist($newTermInfo);
        $this->app['orm.em']->flush();
    }
}