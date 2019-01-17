<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180901000000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("alter table dtb_customer              modify mobilephone01                   longtext                COLLATE utf8_general_ci ;");
        $this->addSql("alter table dtb_customer              modify mobilephone02                   longtext                COLLATE utf8_general_ci ;");
        $this->addSql("alter table dtb_customer              modify mobilephone03                   longtext                COLLATE utf8_general_ci ;");
        $this->addSql("alter table dtb_customer_address      modify mobilephone01                   longtext                COLLATE utf8_general_ci ;");
        $this->addSql("alter table dtb_customer_address      modify mobilephone02                   longtext                COLLATE utf8_general_ci ;");
        $this->addSql("alter table dtb_customer_address      modify mobilephone03                   longtext                COLLATE utf8_general_ci ;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
