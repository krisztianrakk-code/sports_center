<?php
/**
 * User Management and Authentication Class
 * Path: classes/User.php
 */

class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * Regisztráció: Felhasználó létrehozása aktivációs tokennel.
     */
    public function register($name, $email, $phone, $password) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ["success" => false, "message" => "This email is already registered."];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $activationToken = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users (name, email, phone, password, activation_token, is_active, role) VALUES (?, ?, ?, ?, ?, 0, 'registered')";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$name, $email, $phone, $hashedPassword, $activationToken])) {
            return ["success" => true, "token" => $activationToken];
        }
        return ["success" => false, "message" => "Critical storage error during registration."];
    }

    /**
     * Aktiválás: Fiók élesítése token alapján.
     */
    public function activate($token) {
        $sql = "SELECT id FROM users WHERE activation_token = ? AND is_active = 0 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $updateSql = "UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            if ($updateStmt->execute([$user['id']])) {
                return ["success" => true];
            }
        }
        return ["success" => false, "message" => "Invalid or expired activation token."];
    }

    /**
     * Bejelentkezés: Hitelesítés és státuszellenőrzés.
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ["success" => false, "message" => "Invalid authentication credentials."];
        }
        if ($user['is_active'] == 0) {
            return ["success" => false, "message" => "Account inactive. Please verify using your email activation link."];
        }
        if ($user['is_blocked'] == 1) {
            return ["success" => false, "message" => "Access denied. Account is temporarily blocked."];
        }

        return ["success" => true, "user" => $user];
    }

    /**
     * Jelszó-visszaállítás kérése (token generálás).
     */
    public function requestPasswordReset($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            return ["success" => false, "message" => "Active user account not found with this email."];
        }

        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $sql = "UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$resetToken, $expiresAt, $email])) {
            return ["success" => true, "token" => $resetToken];
        }
        return ["success" => false, "message" => "Failed to generate reset link."];
    }

    /**
     * Jelszó tényleges módosítása token alapján.
     */
    public function resetPassword($token, $newPassword) {
        $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return ["success" => false, "message" => "Invalid or expired reset token."];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?";
        $stmt = $this->db->prepare($updateSql);
        
        if ($stmt->execute([$hashedPassword, $user['id']])) {
            return ["success" => true, "message" => "Password successfully updated."];
        }
        return ["success" => false, "message" => "Database error during password update."];
    }

    /**
     * Profil frissítés.
     */
    public function updateProfile($userId, $name, $phone, $password = null) {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $phone, $hashedPassword, $userId]);
        }
        $sql = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $phone, $userId]);
    }

    /**
     * Admin: Blokkolt felhasználók listázása.
     */
    public function getBlockedUsers() {
        $stmt = $this->db->query("SELECT id, name, email, negative_points FROM users WHERE is_blocked = 1");
        return $stmt->fetchAll();
    }

    /**
     * Admin: Blokkolás feloldása.
     */
    public function unblockUser($userId) {
        $sql = "UPDATE users SET is_blocked = 0, negative_points = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
}