<?php

class UserSession
{
    public readonly int $id;
    public readonly string $username;
    public readonly int $loginTimestamp;

    public function __construct(int $id, string $username)
    {
        $this->id = $id;
        $this->username = $username;
        $this->loginTimestamp = time();
    }

    public function getProfileLabel(): string
    {
        return "@" . htmlspecialchars($this->username);
    }
}
?>
