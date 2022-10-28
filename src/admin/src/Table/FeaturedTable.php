<?php
namespace GHSVS\Component\GhsThing\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Featured Table class.
 *
 * @since  1.6
 */
class FeaturedTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   1.6
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__ghsthing_frontpage', 'content_id', $db);
    }
}
