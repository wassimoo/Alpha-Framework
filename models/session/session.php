<?php
namespace Session;
class Session
{
    /* Default session parameters */
    const defaultTimeOut = 60 * 60 * 3; //3 hours
    const defaultRegenIdInterval = 60 * 2; //two minutes

    /** Session parameters that overrides default */
    private $timeOut; /* No limit (server dependent) */
    private $regenIdInterval; /* Maximum 5 hours */

    /** instance properties */
    private $lastActivity;
    private $id_valid_till;
    private $data;

    /**
     * @param int $timeOut session maximum lifetime in seconds
     * @param int $regenIdInterval maximum single session id lifetime in seconds , if expires session_regenerate_id will be called
     * @param array $data array of session lifetime data (to be (un)set on every new session )
     */
    public function __construct($timeOut = self::defaultTimeOut, $regenIdInterval = self::defaultRegenIdInterval, $data = null)
    {
        self::startSession();
        $this->overrideSettings($timeOut, $regenIdInterval, $data);
        $this->initProperties();
    }

    /**
     * Inits session
     * @return void
     */
    public static function startSession()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function overrideSettings($timeOut, $regenIdInterval, $data)
    {
        if ($timeOut > 0) {
            $this->timeOut = $timeOut;
        } else {
            $this->timeOut = self::defaultTimeOut;
            error_log("WARNING (session.php) " . date("Y-m-d H:i:s") . " : Invalid session validity timeout interval was attempted to be set ($timeOut) value has been set to default " . self::defaultTimeOut . "\n", 3, __DIR__ . "/../../logs/session.log");
        }

        if ($regenIdInterval > 0 && $regenIdInterval < 60 * 60 * 5) {
            $this->regenIdInterval = $regenIdInterval;
        } else {
            $this->regenIdInterval = self::defaultRegenIdInterval;
            error_log("WARNING (session.php) " . date("Y-m-d H:i:s") . " : Invalid session regeneration time interval was attempted to be set ($regenIdInterval) value has been set to default " . self::defaultRegenIdInterval . "\n", 3, __DIR__ . "/../../logs/session.log");
        }

        $this->data = $data; //TODO : check vulnerability
    }

    /**
     * Called only by constructor
     * Initialize instance properties
     */
    private function initProperties()
    {
        ini_set('session.use_trans_sid',0);
        ini_set('session.use_strict_mode', 1);
        ini_set('sessions.use_only_cookies',1);
        $this->regenerateId();
        $this->id_valid_till = time() + $this->regenIdInterval;
        $this->lastActivity = time();
    }

    /**
     * @return boolean indicating session valid or not
     */
    public function validate()
    {
        self::startSession(); //make sure session is initialized

        if ($this->lastActivity + $this->timeOut <= time()) {
            $this->endSession();
            return false; //Session expired
        }

        if ($this->id_valid_till <= time()) {
            $this->regenerateId(); //id expired regenerate new one
        }
        return true;
    }

    /**
     * regenrate session id
     */
    public function regenerateId()
    {
        session_regenerate_id();
        $this->id_valid_till = time() + $this->regenIdInterval;
    }

    /** destroy session */
    public function endSession()
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    /********************* Getters ************************/
    public function getLastActivity()
    {
        return $this->lastActivity;
    }
    public function get_id_validity()
    {
        return $this->id_valid_till;
    }
    /******************************************************/
}