<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607172400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE color (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sequence_id INT NOT NULL, send_at DATE NOT NULL COMMENT '(DC2Type:date_immutable)', send_at_time TIME NOT NULL COMMENT '(DC2Type:time_immutable)', subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, attachment LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', is_sent TINYINT(1) NOT NULL, INDEX IDX_B6BD307F98FB19AE (sequence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recipient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_6804FB49A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sequence (id INT AUTO_INCREMENT NOT NULL, label_id INT DEFAULT NULL, user_id INT NOT NULL, user_email_account_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', status VARCHAR(255) NOT NULL, INDEX IDX_5286D72B33B92F39 (label_id), INDEX IDX_5286D72BA76ED395 (user_id), INDEX IDX_5286D72B4ACDEFB8 (user_email_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sequence_recipient (sequence_id INT NOT NULL, recipient_id INT NOT NULL, INDEX IDX_C35DFA4098FB19AE (sequence_id), INDEX IDX_C35DFA40E92F8F78 (recipient_id), PRIMARY KEY(sequence_id, recipient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sequence_label (id INT AUTO_INCREMENT NOT NULL, color_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_752BF9F17ADA1FB5 (color_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sequence_label_user (sequence_label_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_38DCA220BB857F16 (sequence_label_id), INDEX IDX_38DCA220A76ED395 (user_id), PRIMARY KEY(sequence_label_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE signature (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_AE880141A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_email_account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, access_token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) DEFAULT NULL, access_token_expiry DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', provider_user_id VARCHAR(255) DEFAULT NULL, INDEX IDX_2E78933BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_info (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, cp VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B1087D9EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307F98FB19AE FOREIGN KEY (sequence_id) REFERENCES sequence (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence ADD CONSTRAINT FK_5286D72B33B92F39 FOREIGN KEY (label_id) REFERENCES sequence_label (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence ADD CONSTRAINT FK_5286D72BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence ADD CONSTRAINT FK_5286D72B4ACDEFB8 FOREIGN KEY (user_email_account_id) REFERENCES user_email_account (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_recipient ADD CONSTRAINT FK_C35DFA4098FB19AE FOREIGN KEY (sequence_id) REFERENCES sequence (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_recipient ADD CONSTRAINT FK_C35DFA40E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label ADD CONSTRAINT FK_752BF9F17ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label_user ADD CONSTRAINT FK_38DCA220BB857F16 FOREIGN KEY (sequence_label_id) REFERENCES sequence_label (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label_user ADD CONSTRAINT FK_38DCA220A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signature ADD CONSTRAINT FK_AE880141A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_email_account ADD CONSTRAINT FK_2E78933BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_info ADD CONSTRAINT FK_B1087D9EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F98FB19AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72B33B92F39
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72B4ACDEFB8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_recipient DROP FOREIGN KEY FK_C35DFA4098FB19AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_recipient DROP FOREIGN KEY FK_C35DFA40E92F8F78
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label DROP FOREIGN KEY FK_752BF9F17ADA1FB5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label_user DROP FOREIGN KEY FK_38DCA220BB857F16
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sequence_label_user DROP FOREIGN KEY FK_38DCA220A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signature DROP FOREIGN KEY FK_AE880141A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_email_account DROP FOREIGN KEY FK_2E78933BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_info DROP FOREIGN KEY FK_B1087D9EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE color
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recipient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sequence
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sequence_recipient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sequence_label
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sequence_label_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE signature
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_email_account
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_info
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
