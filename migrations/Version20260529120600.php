<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529120600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes fichier et fichier_type sur la table message';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD fichier VARCHAR(255) DEFAULT NULL, ADD fichier_type VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP fichier_type, DROP fichier');
    }
}
