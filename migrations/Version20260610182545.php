<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610182545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_localisation (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, ancienne_latitude VARCHAR(255) DEFAULT NULL, ancienne_longitude VARCHAR(255) DEFAULT NULL, nouvelle_latitude VARCHAR(255) DEFAULT NULL, nouvelle_longitude VARCHAR(255) DEFAULT NULL, statut VARCHAR(50) NOT NULL, date_demande DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', token_validation VARCHAR(255) NOT NULL, INDEX IDX_E81A5CF5B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande_localisation ADD CONSTRAINT FK_E81A5CF5B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_localisation DROP FOREIGN KEY FK_E81A5CF5B1E7706E');
        $this->addSql('DROP TABLE demande_localisation');
    }
}
