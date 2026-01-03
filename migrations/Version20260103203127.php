<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103203127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, nom_restaurant VARCHAR(255) NOT NULL, adresse_restaurant VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, num_tel_restaurant VARCHAR(255) DEFAULT NULL, carte_menu LONGTEXT DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, coord_gps VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, note_consommateurs DOUBLE PRECISION DEFAULT NULL, description_restaurant LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement ADD restaurant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('CREATE INDEX IDX_B26681EB1E7706E ON evenement (restaurant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EB1E7706E');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP INDEX IDX_B26681EB1E7706E ON evenement');
        $this->addSql('ALTER TABLE evenement DROP restaurant_id');
    }
}
