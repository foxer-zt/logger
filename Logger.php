<?php
class Logger
{
    const BACKUP_EXTENSION = '.bak';

    const DATE_FORMAT = '[d/n/y H:i:s]';

    /**
     * Request params.
     *
     * @var array
     */
    private $requestParams = [];

    /**
     * Required request params for each method.
     *
     * @var array
     */
    protected $requiredParams = [
        'log' => ['logFile', 'message'],
        'getLog' => ['logFile'],
        'getLastLogEntry' => ['logFile'],
    ];

    public function __construct()
    {
        $this->requestParams = $_GET;
    }

    /**
     * Write data into log file.
     *
     * @return void
     */
    public function log()
    {
        $this->validateRequest();
        $logFile = $this->requestParams['logFile'];
        $message = $this->requestParams['message'];
        $logFile = $this->createLogFile($logFile);
        $newLogEntry = isset($this->requestParams['withoutDate'])
            ? $message
            : sprintf("%s: %s", date(self::DATE_FORMAT, time()), $message);
        $content = file_get_contents($logFile) . "\n$newLogEntry";
        if (!file_put_contents($logFile, $content)) {
            throw new Exception("Unable to read/write into $logFile file.");
        }
    }

    /**
     * Retrieve log data.
     *
     * @return string
     */
    public function getLog()
    {
        $this->validateRequest();
        return file_get_contents($this->requestParams['logFile'] . self::BACKUP_EXTENSION);
    }

    /**
     * Get last item in log file.
     *
     * @@return string
     */
    public function getLastLogEntry()
    {
        $this->validateRequest();
        preg_match_all("@\[.*\]:\s.*@", $this->getLog(), $matches);
        return array_pop($matches[0]);
    }

    /**
     * Create file for logging purpose if it's does'nt exists.
     * E.g. $fileName = default will create default.bak file.
     *
     * @param string $fileName
     * @return string
     */
    private function createLogFile($fileName)
    {
        $file = $fileName . self::BACKUP_EXTENSION;
        if (!file_exists($file)) {
            if (!touch($file)){
                throw new Exception("Unable to create $file file.");
            }
        }
        return $file;
    }

    /**
     * Validate passed request params.
     *
     * @throws Exception
     * @return void
     */
    private function validateRequest()
    {
        $calledMethod = debug_backtrace()[1]['function'];
        if (in_array($calledMethod, array_keys($this->requiredParams))) {
            foreach ($this->requiredParams[$calledMethod] as $requiredParam) {
                if (!in_array($requiredParam, array_keys($this->requestParams))) {
                    throw new Exception("Missed required param $requiredParam for method $calledMethod.");
                }
            }
        }
    }
}
