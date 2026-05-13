<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Stripe fields to order table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` ADD stripe_session_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP COLUMN stripe_session_id');
        $this->addSql('ALTER TABLE `order` DROP COLUMN stripe_payment_intent_id');
    }
}
