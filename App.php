<?php

class App
{
    const DICTIONARY = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    protected $reasons = array(
        200 => 'OK',
        302 => 'Found',
        400 => 'Bad Request',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    );

    public function actionIndex()
    {
        $body = file_get_contents(ROOTDIR . '/views/index.html');
        $this->echoResponse($body, 200, array('Content-Type' => 'text/html'));
    }

    public function actionShorten()
    {
        // Check url for empty
        if (empty($_POST['url'])) {
            $this->echoResponse('URL parameter is required', 400);
        }

        // Validating URL
        $url = filter_var(trim($_POST['url']), FILTER_VALIDATE_URL);

        if (!$url) {
            $this->echoResponse('Invalid URL', 400);
        }

        try {
            // Connect to DB
            $db = $this->getDb();

            // Checking URL
            if ($record = $this->getRecordByUrl($db, $url)) {
                // URL exists
                $short = $record['short'];
            } else {
                // URL not exists, inserting
                $insertQuery = $db->prepare('INSERT INTO urls (short, url) VALUES (NULL, :url)');
                $insertQuery->bindValue(':url', $url);
                $insertQuery->execute();

                // Getting last inserted ID
                $id = $db->lastInsertId('id');
                $short = $this->generateShort($id);

                // Updating record with short value
                $updateQuery = $db->prepare('UPDATE urls SET short = :short WHERE id = :id');
                $updateQuery->bindValue(':short', $short);
                $updateQuery->bindValue(':id', $id, PDO::PARAM_INT);
                $updateQuery->execute();
            }

            // Returning short URL to client
            $this->echoResponse($short, 200, array('Content-Type' => 'text/plain'));
        } catch (PDOException $e) {
            // Something goes wrong with DB
            $this->echoResponse('DB Error: ' . $e->getMessage(), 500);
        }
    }

    public function actionRedirect($short)
    {
        try {
            $db = $this->getDb();

            if ($record = $this->getRecordByShort($db, $short)) {
                $this->echoResponse(null, 302, array('Location' => $record['url']));
            } else {
                $this->echoResponse('URL not found', 404);
            }
        } catch (PDOException $e) {
            $this->echoResponse('DB Error: ' . $e->getMessage(), 500);
        }
    }

    public function routeNotFound()
    {
        $this->echoResponse('Page not found', 404);
    }

    /**
     * Echoes HTTP response
     *
     * @param string|null $body
     * @param int $status
     * @param array $headers
     */
    private function echoResponse($body = null, $status = 200, array $headers = array())
    {
        // Status line
        header(sprintf('HTTP/1.1 %s %s', $status, $this->reasons[$status]));

        // Headers
        foreach ($headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        // Body
        if ($body) {
            echo $body;
        }

        die();
    }

    /**
     * Generates shot URL using bijective function
     *
     * @link http://en.wikipedia.org/wiki/Bijection
     * @param int $i
     * @return string
     */
    private function generateShort($i)
    {
        $dictionary = str_split(self::DICTIONARY);

        if ($i == 0) {
            return $dictionary[0];
        }

        $result = [];
        $base = count($dictionary);

        while ($i > 0) {
            $result[] = $dictionary[($i % $base)];
            $i = floor($i / $base);
        }

        $result = array_reverse($result);

        return join('', $result);
    }

    /**
     * Creates DB instance
     *
     * @return PDO
     */
    private function getDb()
    {
        $host = getenv('DB_HOST');
        $name = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        return new PDO(sprintf("mysql:host=%s;dbname=%s", $host, $name), $user, $pass);
    }

    /**
     * Looking for a record with a given URL
     *
     * @param PDO $db
     * @param string $url
     * @return array
     */
    private function getRecordByUrl(PDO $db, $url)
    {
        $query = $db->prepare('SELECT id, short, url FROM urls WHERE BINARY url = :url');
        $query->bindValue(':url', $url);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Looking for a record with a given short URL
     *
     * @param PDO $db
     * @param string $short
     * @return array
     */
    private function getRecordByShort(PDO $db, $short)
    {
        $query = $db->prepare('SELECT id, short, url FROM urls WHERE BINARY short = :short');
        $query->bindValue(':short', $short);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}