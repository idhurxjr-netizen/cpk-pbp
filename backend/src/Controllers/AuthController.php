<?php
declare(strict_types=1);

final class AuthController
{
    public function __construct(private array $config)
    {
    }

    public function register(): void
    {
        $data = $this->jsonBody();

        $name = trim((string) ($data['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $phone = trim((string) ($data['phone'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors['name'] = 'Nama harus 2-100 karakter.';
        } elseif (preg_match('/\d/u', $name)) {
            $errors['name'] = 'Nama tidak boleh mengandung angka.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $errors['email'] = 'Format email tidak valid.';
        }

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            $errors['phone'] = 'Nomor HP harus 10-15 digit angka.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Password maksimal 72 karakter.';
        }

        $users = new User($this->pdo());

        if (!isset($errors['email']) && $users->findByEmail($email) !== null) {
            $errors['email'] = 'Email sudah terdaftar.';
        }

        if (!isset($errors['phone']) && $users->findByPhone($phone) !== null) {
            $errors['phone'] = 'Nomor HP sudah terdaftar.';
        }

        if ($errors !== []) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        try {
            $user = $users->create([
                'id' => Ulid::generate(),
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                Response::error('Email atau nomor HP sudah terdaftar.', 409);
            }

            error_log($e->getMessage());
            Response::error('Gagal membuat akun.', 500);
        }

        if ($user === []) {
            Response::error('Gagal membuat akun.', 500);
        }

        Response::json([
            'data' => [
                'user' => $user,
                'token' => $this->issueToken($user),
            ],
        ], 201);
    }

    public function login(): void
    {
        $data = $this->jsonBody();

        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            Response::error('Email dan password wajib diisi.', 422);
        }

        $users = new User($this->pdo());
        $user = $users->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            Response::error('Email atau password salah.', 401);
        }

        $publicUser = $users->findById($user['id']);

        if ($publicUser === null) {
            Response::error('Pengguna tidak ditemukan.', 404);
        }

        Response::json([
            'data' => [
                'user' => $publicUser,
                'token' => $this->issueToken($publicUser),
            ],
        ]);
    }

    public function me(): void
    {
        $user = Auth::user($this->pdo(), $this->config);
        Response::json(['data' => ['user' => $user]]);
    }

    public function deleteMe(): void
    {
        $user = Auth::user($this->pdo(), $this->config);
        $data = $this->jsonBody();
        $password = (string) ($data['password'] ?? '');

        if ($password === '') {
            Response::error('Password wajib diisi untuk menghapus akun.', 422);
        }

        $users = new User($this->pdo());
        $fullUser = $users->findWithPasswordById($user['id']);

        if ($fullUser === null || !password_verify($password, $fullUser['password_hash'])) {
            Response::error('Password salah.', 401);
        }

        $users->delete($user['id']);
        Response::noContent();
    }

    private function issueToken(array $user): string
    {
        $now = time();

        return Jwt::encode([
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => $now,
            'exp' => $now + (int) $this->config['jwt_ttl'],
        ], (string) $this->config['jwt_secret']);
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw !== false ? $raw : '', true);

        if (!is_array($data)) {
            Response::error('Body harus berupa JSON yang valid.', 400);
        }

        return $data;
    }

    private function pdo(): PDO
    {
        return DB::connect($this->config['db']);
    }
}