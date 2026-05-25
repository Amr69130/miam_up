<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525133857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table utilisateur_favoris (favoris restaurants par utilisateur)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur_favoris (utilisateur_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_F33833A0FB88E14F (utilisateur_id), INDEX IDX_F33833A0B1E7706E (restaurant_id), PRIMARY KEY(utilisateur_id, restaurant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE utilisateur_favoris ADD CONSTRAINT FK_F33833A0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_favoris ADD CONSTRAINT FK_F33833A0B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur_favoris DROP FOREIGN KEY FK_F33833A0FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_favoris DROP FOREIGN KEY FK_F33833A0B1E7706E');
        $this->addSql('DROP TABLE utilisateur_favoris');
    }
}
