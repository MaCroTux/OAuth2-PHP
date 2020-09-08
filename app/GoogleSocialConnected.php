<?php

class GoogleSocialConnected extends SocialConnected
{
    private const URL_GOOGLE_APIS = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=%s";
    private const FILE_DATA = "oauth_users/%s.json";

    /** @var string */
    private $accessToken;
    /**
     * @var string
     */
    private $userId;
    /**
     * @var string
     */
    private $name;

    public function __construct(
        string $accessToken,
        string $userId,
        string $name,
        FileDataRepository $fileDataRepository
    ) {
        parent::__construct($fileDataRepository);

        $this->accessToken = $accessToken;
        $this->userId = $userId;
        $this->name = $name;
    }

    public function saveClientData()
    {
        $client = $this->authenticateClient();
        $email = $client['email'];

        $data = [
            'id' => $this->userId,
            'social' => 'google',
            'name' => base64_decode($this->name),
            'email' => $email,
            'visit' => [time()],
        ];

        if ($this->hasClient($this->userId)) {
            $this->addVisitClient();
        }else{
            $this->fileDataRepository->saveDataInJson($data);
        }
    }

    private function addVisitClient(): void
    {
        $this->fileDataRepository->putField('visit', time());
    }

    private function hasClient(string $id): bool
    {
        return $this->fileDataRepository->isFileExist();
    }


    private function authenticateClient(): ?array
    {
        $url = sprintf(self::URL_GOOGLE_APIS, $this->accessToken);
        $result = $this->request($url);
        $client = json_decode($result, 1);

        if (!empty($client['email'])) {
            return $client;
        }

        return null;
    }
}
