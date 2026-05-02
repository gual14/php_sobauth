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
