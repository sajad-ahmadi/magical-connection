<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\DTO;

use MagicalConnection\Modules\Connection\Services\ConnectionFactory;
use MagicalConnection\Support\Url;

/**
 * Represent a Magical Connection configuration and its credentials.
 *
 * Provides typed accessors for connection metadata, transfer settings,
 * authentication credentials, and derived connection paths.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionDTO
{
    private $id = 0;
    private $name = "";
    private $protocol = "";
    private $domain = "";
    private $host = "";
    private $port = 21;
    private $base_path = "";
    private $timeout = 60;
    private $status = 0;
    private $transferred_files = 0;
    private $passive_mod = 0;
    private $created_at = "";
    private $updated_at = "";


    private $username = "";
    private $password = "";
    private $credentials_id = 0;
    private $private_key = "";
    private $passphrase = "";
    private $token = "";

    /**
     * Convert the connection configuration to an associative array.
     *
     * Includes connection metadata and stored authentication credentials.
     *
     * @since 1.0.0
     *
     * @return array Connection configuration data.
     */
    public function toArray(): array
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'protocol'          => $this->getProtocol(),
            'domain'            => $this->getDomain(),
            'host'              => $this->getHost(),
            'port'              => $this->getPort(),
            'base_path'         => $this->getBasePath(),
            'timeout'           => $this->getTimeout(),
            'status'            => $this->getStatus(),
            'transferred_files' => $this->getTransferredFiles(),
            'passive_mod'       => $this->getPassiveMod(),
            'created_at'        => $this->getCreatedAt(),
            'updated_at'        => $this->getUpdatedAt(),
            'username'          => $this->getUsername(),
            'password'          => $this->getPassword(),
            'credentials_id'    => $this->getCredentialsId(),
            'private_key'       => $this->getPrivateKey(),
            'passphrase'        => $this->getPassphrase(),
            'token'             => $this->getToken(),
        ];
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol)
    {
        if (in_array($protocol, ConnectionFactory::$PROTOCOLS)) $this->protocol = $protocol;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }

    public function getBasePath(): string
    {
        return $this->base_path;
    }

    public function setBasePath(string $base_path)
    {
        $this->base_path = $base_path;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getTransferredFiles(): int
    {
        return $this->transferred_files;
    }

    public function setTransferredFiles(int $transferred_files)
    {
        $this->transferred_files = $transferred_files;
    }

    public function getPassiveMod(): bool
    {
        return (bool)$this->passive_mod;
    }

    public function setPassiveMod($passive_mod)
    {
        $this->passive_mod = (bool)$passive_mod;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getCredentialsId(): int
    {
        return $this->credentials_id;
    }

    public function setCredentialsId(int $credentials_id)
    {
        $this->credentials_id = $credentials_id;
    }

    public function getPrivateKey(): string
    {
        return $this->private_key;
    }

    public function setPrivateKey(string $private_key)
    {
        $this->private_key = $private_key;
    }

    public function getPassphrase(): string
    {
        return $this->passphrase;
    }

    public function setPassphrase(string $passphrase)
    {
        $this->passphrase = $passphrase;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getFullUrl(string $file_path = ""): string
    {
        return Url::normalize($this->getDomain() . "/" . $file_path);
    }

    public function getFullPath(string $file_path = ""): string
    {
        return Url::normalize($this->getBasePath() . $file_path);
    }
}