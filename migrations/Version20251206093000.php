<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251206093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name, last_name and telephone columns to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `user` ADD COLUMN `name` VARCHAR(100) DEFAULT NULL, ADD COLUMN `last_name` VARCHAR(100) DEFAULT NULL, ADD COLUMN `telephone` VARCHAR(100) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN `name`, DROP COLUMN `last_name`, DROP COLUMN `telephone`');
    }
}
