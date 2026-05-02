<?php
declare(strict_types=1);

class Usuario {
    private $id;
    private $username;
    private $email;
    private $nombre;
    private $apellidos;
    private $password_hash;
    private $rol;
    private $avatar_tipo;
    private $avatar_valor;
    private $avatar_url;
    private $activo;
    private $updated_at;
    private $bistrocoins;


    public function __construct(int $id, string $username, string $email, string $nombre, string $apellidos, 
                                string $password_hash, string $rol, string $avatar_tipo, ?string $avatar_valor, 
                                string $avatar_url, int $activo, string $updated_at, int $bistrocoins = 0) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->password_hash = $password_hash;
        $this->rol = $rol;
        $this->avatar_tipo = $avatar_tipo;
        $this->avatar_valor = $avatar_valor;
        $this->avatar_url = $avatar_url;
        $this->activo = $activo;
        $this->updated_at = $updated_at;
        $this->bistrocoins = $bistrocoins;

    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getPasswordHash(): string { return $this->password_hash; }
    public function getRol(): string { return $this->rol; }
    public function getAvatarTipo(): string { return $this->avatar_tipo; }
    public function getAvatarValor(): ?string { return $this->avatar_valor; }
    public function getAvatarUrl(): string { return $this->avatar_url; }
    public function getActivo(): int { return $this->activo; }
    public function getUpdatedAt(): string { return $this->updated_at; }
    public function isActivo(): bool { return $this->activo === 1; }
    public function getNombreCompleto(): string { return $this->nombre . ' ' . $this->apellidos; }
    public function getBistrocoins(): int { return $this->bistrocoins; }
}