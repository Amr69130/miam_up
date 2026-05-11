<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511132003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE amenagement (id INT AUTO_INCREMENT NOT NULL, nom_amenagement VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE amitie (id INT AUTO_INCREMENT NOT NULL, utilisateur_demandeur_id INT NOT NULL, utilisateur_receveur_id INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_8FF9F39CFC54CAAA (utilisateur_demandeur_id), INDEX IDX_8FF9F39C4D81EBB5 (utilisateur_receveur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, evenement_id INT DEFAULT NULL, nom_conversation VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_8A8E26E9FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_utilisateur (conversation_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_F3031EC39AC0396 (conversation_id), INDEX IDX_F3031EC3FB88E14F (utilisateur_id), PRIMARY KEY(conversation_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, createur_id INT DEFAULT NULL, date_evenement DATETIME NOT NULL, description_evenement LONGTEXT DEFAULT NULL, nombre_places INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, date_fin DATETIME DEFAULT NULL, INDEX IDX_B26681EB1E7706E (restaurant_id), INDEX IDX_B26681E73A201E5 (createur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_utilisateur (evenement_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_8C897598FD02F13 (evenement_id), INDEX IDX_8C897598FB88E14F (utilisateur_id), PRIMARY KEY(evenement_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, expediteur_id INT DEFAULT NULL, date_message DATETIME NOT NULL, contenu LONGTEXT NOT NULL, status_message VARCHAR(255) NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307F10335F61 (expediteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, date_paiement DATETIME NOT NULL, id_transaction VARCHAR(255) NOT NULL, montant INT NOT NULL, status_paiement VARCHAR(255) NOT NULL, INDEX IDX_B1DC7A1EFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE regime (id INT AUTO_INCREMENT NOT NULL, nom_regime VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, nom_restaurant VARCHAR(255) NOT NULL, adresse_restaurant VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, num_tel_restaurant VARCHAR(255) DEFAULT NULL, carte_menu LONGTEXT DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, note_consommateurs DOUBLE PRECISION DEFAULT NULL, description_restaurant LONGTEXT DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_specialite (restaurant_id INT NOT NULL, specialite_id INT NOT NULL, INDEX IDX_42ECDCF0B1E7706E (restaurant_id), INDEX IDX_42ECDCF02195E0F0 (specialite_id), PRIMARY KEY(restaurant_id, specialite_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_amenagement (restaurant_id INT NOT NULL, amenagement_id INT NOT NULL, INDEX IDX_1E8E6E3BB1E7706E (restaurant_id), INDEX IDX_1E8E6E3B4CCE776B (amenagement_id), PRIMARY KEY(restaurant_id, amenagement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_regime (restaurant_id INT NOT NULL, regime_id INT NOT NULL, INDEX IDX_5B18B58DB1E7706E (restaurant_id), INDEX IDX_5B18B58D35E7D534 (regime_id), PRIMARY KEY(restaurant_id, regime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_type (restaurant_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_4EC8C74DB1E7706E (restaurant_id), INDEX IDX_4EC8C74DC54C8C93 (type_id), PRIMARY KEY(restaurant_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, nom_role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialite (id INT AUTO_INCREMENT NOT NULL, nom_specialite VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, nom_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom_utilisateur VARCHAR(255) NOT NULL, prenom_utilisateur VARCHAR(255) NOT NULL, email_utilisateur VARCHAR(255) NOT NULL, pseudo_utilisateur VARCHAR(255) NOT NULL, photo_profil VARCHAR(255) DEFAULT NULL, description_utilisateur VARCHAR(255) DEFAULT NULL, note_utilisateur VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, num_utilisateur INT DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_role (utilisateur_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_9EE8E650FB88E14F (utilisateur_id), INDEX IDX_9EE8E650D60322AC (role_id), PRIMARY KEY(utilisateur_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_favoris (utilisateur_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_F33833A0FB88E14F (utilisateur_id), INDEX IDX_F33833A0B1E7706E (restaurant_id), PRIMARY KEY(utilisateur_id, restaurant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amitie ADD CONSTRAINT FK_8FF9F39CFC54CAAA FOREIGN KEY (utilisateur_demandeur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE amitie ADD CONSTRAINT FK_8FF9F39C4D81EBB5 FOREIGN KEY (utilisateur_receveur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE conversation_utilisateur ADD CONSTRAINT FK_F3031EC39AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_utilisateur ADD CONSTRAINT FK_F3031EC3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE evenement_utilisateur ADD CONSTRAINT FK_8C897598FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_utilisateur ADD CONSTRAINT FK_8C897598FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE restaurant_specialite ADD CONSTRAINT FK_42ECDCF0B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_specialite ADD CONSTRAINT FK_42ECDCF02195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_amenagement ADD CONSTRAINT FK_1E8E6E3BB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_amenagement ADD CONSTRAINT FK_1E8E6E3B4CCE776B FOREIGN KEY (amenagement_id) REFERENCES amenagement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_regime ADD CONSTRAINT FK_5B18B58DB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_regime ADD CONSTRAINT FK_5B18B58D35E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_type ADD CONSTRAINT FK_4EC8C74DB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_type ADD CONSTRAINT FK_4EC8C74DC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_role ADD CONSTRAINT FK_9EE8E650FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_role ADD CONSTRAINT FK_9EE8E650D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_favoris ADD CONSTRAINT FK_F33833A0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_favoris ADD CONSTRAINT FK_F33833A0B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE amitie DROP FOREIGN KEY FK_8FF9F39CFC54CAAA');
        $this->addSql('ALTER TABLE amitie DROP FOREIGN KEY FK_8FF9F39C4D81EBB5');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9FD02F13');
        $this->addSql('ALTER TABLE conversation_utilisateur DROP FOREIGN KEY FK_F3031EC39AC0396');
        $this->addSql('ALTER TABLE conversation_utilisateur DROP FOREIGN KEY FK_F3031EC3FB88E14F');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EB1E7706E');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E73A201E5');
        $this->addSql('ALTER TABLE evenement_utilisateur DROP FOREIGN KEY FK_8C897598FD02F13');
        $this->addSql('ALTER TABLE evenement_utilisateur DROP FOREIGN KEY FK_8C897598FB88E14F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EFB88E14F');
        $this->addSql('ALTER TABLE restaurant_specialite DROP FOREIGN KEY FK_42ECDCF0B1E7706E');
        $this->addSql('ALTER TABLE restaurant_specialite DROP FOREIGN KEY FK_42ECDCF02195E0F0');
        $this->addSql('ALTER TABLE restaurant_amenagement DROP FOREIGN KEY FK_1E8E6E3BB1E7706E');
        $this->addSql('ALTER TABLE restaurant_amenagement DROP FOREIGN KEY FK_1E8E6E3B4CCE776B');
        $this->addSql('ALTER TABLE restaurant_regime DROP FOREIGN KEY FK_5B18B58DB1E7706E');
        $this->addSql('ALTER TABLE restaurant_regime DROP FOREIGN KEY FK_5B18B58D35E7D534');
        $this->addSql('ALTER TABLE restaurant_type DROP FOREIGN KEY FK_4EC8C74DB1E7706E');
        $this->addSql('ALTER TABLE restaurant_type DROP FOREIGN KEY FK_4EC8C74DC54C8C93');
        $this->addSql('ALTER TABLE utilisateur_role DROP FOREIGN KEY FK_9EE8E650FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_role DROP FOREIGN KEY FK_9EE8E650D60322AC');
        $this->addSql('ALTER TABLE utilisateur_favoris DROP FOREIGN KEY FK_F33833A0FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_favoris DROP FOREIGN KEY FK_F33833A0B1E7706E');
        $this->addSql('DROP TABLE amenagement');
        $this->addSql('DROP TABLE amitie');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_utilisateur');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_utilisateur');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE regime');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE restaurant_specialite');
        $this->addSql('DROP TABLE restaurant_amenagement');
        $this->addSql('DROP TABLE restaurant_regime');
        $this->addSql('DROP TABLE restaurant_type');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE specialite');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE utilisateur_role');
        $this->addSql('DROP TABLE utilisateur_favoris');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
