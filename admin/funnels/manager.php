<?php


class Brizy_Admin_Funnels_Manager extends Brizy_Admin_Entity_AbstractManager
{

    /**
     * @var
     */
    private $type;

    /**
     * Brizy_Admin_Blocks_Manager constructor.
     *
     * @param $type
     *
     * @throws Exception
     */
    public function __construct($type)
    {
        if ( ! self::isSupportedPostType($type)) {
            throw new Exception('Unsupported funnel post type');
        }

        $this->type = $type;
    }

    public static function isSupportedPostType($type)
    {
        return in_array($type, [Brizy_Admin_Funnels_Main::CP_FUNNEL_PAGE, Brizy_Admin_Funnels_Main::CP_FUNNEL_POPUP]);
    }

    public function getPostIdByUid($uid)
    {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id 
                       FROM $wpdb->postmeta 
                       WHERE meta_key = 'brizy_post_uid' AND  meta_value = %s LIMIT 1",
                array($uid)
            )
        );
    }

    /**
     * @param $args
     *
     * @return array|Brizy_Editor_Block|Brizy_Editor_Post|mixed|null
     */
    public function getEntities($args)
    {
        $posts =  $this->getEntitiesByType($this->type, $args);
    }

    public static function sortByPosition($posts) {
        usort(
            $posts,
            function ($a, $b) {
                /**
                 * @var Brizy_Editor_FunnelPopup $a ;
                 * @var Brizy_Editor_FunnelPopup $b ;
                 */
                if ($a->getFunnelMeta()->position == $b->getFunnelMeta()->position) {
                    return 0;
                }

                return $a->getFunnelMeta()->position > $b->getFunnelMeta()->position ? -1 : 1;
            }
        );

        return $posts;
    }

    public function getEntitiesByParent($parent, $args)
    {
        $filterArgs = array(
            'post_parent'    => (int)$parent,
            'post_type'      => [Brizy_Admin_Funnels_Main::CP_FUNNEL_PAGE, Brizy_Admin_Funnels_Main::CP_FUNNEL_POPUP],
            'posts_per_page' => -1,
            'post_status'    => get_post_stati(),
        );
        $filterArgs = array_merge($filterArgs, $args);

        $posts = get_posts($filterArgs);

        $entities = [];
        foreach ($posts as $apost) {
            $entities[] = $this->convertWpPostToEntity($apost);
        }

        $entities = self::sortByPosition($entities);

        return $entities;
    }

    /**
     * @param $uid
     *
     * @return Brizy_Editor_Entity|null
     * @throws Exception
     */
    public function getEntity($uid)
    {
        return $this->getEntityUidAndType($uid, $this->type);
    }

    /**
     * @param $uid
     * @param string $status
     * @param null $properties
     *
     * @return mixed|null
     */
    public function createEntity($uid, $status = 'publish', $properties = null)
    {
        return $this->createEntityByType($uid, $this->type, $status, $properties);
    }


    /**
     * @param $post
     * @param null $uid
     *
     * @return Brizy_Editor_FunnelPage|Brizy_Editor_FunnelPopup|Brizy_Editor_Post|mixed
     * @throws Exception
     */
    protected function convertWpPostToEntity($post, $uid = null)
    {
        return Brizy_Editor_Entity::get($post);
    }
}
