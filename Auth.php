<?php
require_once "Session.php";
require_once "UserSession.php";

class Auth
{
    private $db;
    private $session;
    private ?UserSession $user = null;
    public function __construct(PDO $pdo, Session $session)
    {
        $this->db = $pdo;
        $this->session = $session;
    }

    // return true if registration is successful, false if user already exists or other error occurs
    public function register(string $username, string $password): bool
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password) VALUES (:username, :password)",
            );
            return $stmt->execute([
                "username" => $username,
                "password" => $hashedPassword,
            ]);
        } catch (PDOException $e) {
            // 23000 = unique constraint violation
            if ($e->getCode() === 23000) {
                return false;
            }
            throw $e;
        }
    }

    public function login(string $username, string $password): bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE username = :username",
        );
        $stmt->execute(["username" => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user["password"])) {
            $this->session->regenerate();
            $sessionData = new UserSession($user["id"], $user["username"]);
            $this->session->set("auth_user", $sessionData);
            $this->user = $sessionData;

            return true;
        }
        return false;
    }

    public function user(): ?UserSession
    {
        if ($this->user === null) {
            $this->user = $this->session->get("auth_user");
        }
        return $this->user;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->remove("auth_user");
        $this->session->destroy();
    }

    public function requireAuth(string $redirectTo = "login.php"): void
    {
        if ($this->user() === null) {
            header("Location: $redirectTo");
            exit();
        }
    }
}

?>
