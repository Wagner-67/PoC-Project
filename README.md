---
# Symfony Auth PoC

Ein leichtgewichtiges Backend-PoC mit Symfony für User-Authentifizierung, JWTs und Passwort-Resets.

---

## Inhaltsverzeichnis

- [Beschreibung](#beschreibung)
- [Tech-Stack & Bundles](#tech-stack--bundles)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Datenbank](#datenbank)
- [Start](#start)
- [API Endpunkte](#api-endpunkte)
- [Tests](#tests)
- [Lizenz](#lizenz)

---

## Beschreibung

Dieses Proof of Concept zeigt ein einfaches Symfony-Backend mit folgenden Features:

- **User Registration**
  - Registrierung mit einzigartiger E-Mail, Name und Passwort + Bestätigung
- **Authentifizierung**
  - Ausgabe eines JWT-Tokens (Gültigkeit: 15 Minuten)
  - Refresh Token (Gültigkeit: 7 Tage) zum Nachladen des JWT
- **Passwort-Reset**
  - Passwort-Wiederherstellung per E-Mail-Anfrage (Sicherheitsmeldung ohne Rückschluss auf Existenz der Adresse)
  - Versand eines einmal nutzbaren Reset-Tokens (Gültigkeit: 15 Minuten) per Link
- **Sicherheit & Rate Limiting**
  - Rate Limiter für die Login-Route: maximal 5 Anfragen pro Minute

---

## Tech-Stack & Bundles

- PHP 8.1+
- Symfony 6.x
- Doctrine ORM & Migrations
- LexikJWTAuthenticationBundle
- GesdinetJWTRefreshTokenBundle
- JMSSerializerBundle
- MakerBundle (nur dev)

```php
// config/bundles.php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle::class => ['all' => true],
];
