<?php


class Brizy_Editor_FunnelPopup extends Brizy_Editor_Popup
{
    use Brizy_Admin_Funnels_PositionAware;

    static protected $instance = null;

    /**
     * @param $apost
     *
     * @return Brizy_Editor_Post|mixed
     * @throws Exception
     */
    public static function get($apost, $uid = null)
    {

        $wp_post_id = $apost;
        if ($apost instanceof WP_Post) {
            $wp_post_id = $apost->ID;
        }

        if (isset(self::$instance[$wp_post_id])) {
            return self::$instance[$wp_post_id];
        }

        return self::$instance[$wp_post_id] = new self($wp_post_id);
    }

    /**
     * Clear all cached instances;
     */
    public static function cleanClassCache()
    {
        self::$instance = array();
    }

    /**
     * @return bool
     * @todo rename this to isGlobal
     *
     */
    public function isGlobalPopup()
    {
        return false;
    }

    public function isSavedPopup()
    {
        return false;
    }

    public function isCloudUpdateRequired()
    {
        return false;
    }

    public function setCloudUpdateRequired($flag)
    {
        return throw new Exception('Cloud Update is not supported for Funnel Popups');
    }

    public function setCloudId($flag)
    {
        return throw new Exception('Cloud Update is not supported for Funnel Popups');
    }

    public function setContainer($flag)
    {
        return throw new Exception('Cloud Update is not supported for Funnel Popups');
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        unset($data['cloudId']);

        return $data;
    }

    public function convertToOptionValue()
    {
        $data = parent::convertToOptionValue();
        unset($data['cloudId']);

        return $data;
    }
}