<?php
namespace GHSVS\Component\GhsThing\Site\Service;

use Joomla\CMS\Categories\Categories;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   1.6
     */
    public function __construct($options = array())
    {
        $options['table']      = '#__ghsthing';
        $options['extension']  = 'com_ghsthing';
        $options['statefield'] = 'state';

        parent::__construct($options);
    }
}
