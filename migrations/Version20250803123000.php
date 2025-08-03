<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250803123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_tokens table with correct columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE refresh_tokens (
            id INT AUTO_INCREMENT NOT NULL,
            refresh_tokens VARCHAR(255) NOT NULL,
            username VARCHAR(180) NOT NULL,
            valid_until DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_REFRESH_TOKEN (refresh_token),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
