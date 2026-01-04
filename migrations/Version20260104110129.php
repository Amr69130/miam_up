<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104110129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE amenagement (id INT AUTO_INCREMENT NOT NULL, nom_amenagement VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE regime (id INT AUTO_INCREMENT NOT NULL, nom_regime VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_specialite (restaurant_id INT NOT NULL, specialite_id INT NOT NULL, INDEX IDX_42ECDCF0B1E7706E (restaurant_id), INDEX IDX_42ECDCF02195E0F0 (specialite_id), PRIMARY KEY(restaurant_id, specialite_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_amenagement (restaurant_id INT NOT NULL, amenagement_id INT NOT NULL, INDEX IDX_1E8E6E3BB1E7706E (restaurant_id), INDEX IDX_1E8E6E3B4CCE776B (amenagement_id), PRIMARY KEY(restaurant_id, amenagement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_regime (restaurant_id INT NOT NULL, regime_id INT NOT NULL, INDEX IDX_5B18B58DB1E7706E (restaurant_id), INDEX IDX_5B18B58D35E7D534 (regime_id), PRIMARY KEY(restaurant_id, regime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_type (restaurant_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_4EC8C74DB1E7706E (restaurant_id), INDEX IDX_4EC8C74DC54C8C93 (type_id), PRIMARY KEY(restaurant_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialite (id INT AUTO_INCREMENT NOT NULL, nom_specialite VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, nom_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE restaurant_specialite ADD CONSTRAINT FK_42ECDCF0B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_specialite ADD CONSTRAINT FK_42ECDCF02195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_amenagement ADD CONSTRAINT FK_1E8E6E3BB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_amenagement ADD CONSTRAINT FK_1E8E6E3B4CCE776B FOREIGN KEY (amenagement_id) REFERENCES amenagement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_regime ADD CONSTRAINT FK_5B18B58DB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_regime ADD CONSTRAINT FK_5B18B58D35E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_type ADD CONSTRAINT FK_4EC8C74DB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_type ADD CONSTRAINT FK_4EC8C74DC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant_specialite DROP FOREIGN KEY FK_42ECDCF0B1E7706E');
        $this->addSql('ALTER TABLE restaurant_specialite DROP FOREIGN KEY FK_42ECDCF02195E0F0');
        $this->addSql('ALTER TABLE restaurant_amenagement DROP FOREIGN KEY FK_1E8E6E3BB1E7706E');
        $this->addSql('ALTER TABLE restaurant_amenagement DROP FOREIGN KEY FK_1E8E6E3B4CCE776B');
        $this->addSql('ALTER TABLE restaurant_regime DROP FOREIGN KEY FK_5B18B58DB1E7706E');
        $this->addSql('ALTER TABLE restaurant_regime DROP FOREIGN KEY FK_5B18B58D35E7D534');
        $this->addSql('ALTER TABLE restaurant_type DROP FOREIGN KEY FK_4EC8C74DB1E7706E');
        $this->addSql('ALTER TABLE restaurant_type DROP FOREIGN KEY FK_4EC8C74DC54C8C93');
        $this->addSql('DROP TABLE amenagement');
        $this->addSql('DROP TABLE regime');
        $this->addSql('DROP TABLE restaurant_specialite');
        $this->addSql('DROP TABLE restaurant_amenagement');
        $this->addSql('DROP TABLE restaurant_regime');
        $this->addSql('DROP TABLE restaurant_type');
        $this->addSql('DROP TABLE specialite');
        $this->addSql('DROP TABLE type');
    }
}
