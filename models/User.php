<?php
class User
{
    private PDO $conn;
    public function __construct(PDO $conn) { $this->conn = $conn; }
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->conn->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!password_verify($password, $hash) || !$user) return null;
        return ['id' => (int)$user['id'], 'username' => $user['username']];
    }
}
