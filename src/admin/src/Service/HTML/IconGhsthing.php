<?php
namespace GHSVS\Component\GhsThing\Administrator\Service\HTML;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component HTML Helper
 *
 * @since  4.0.0
 */
class IconGhsthing
{
    /**
     * The user factory
     *
     * @var    UserFactoryInterface
     *
     * @since  4.2.0
     */
    private $userFactory;

    /**
     * Service constructor
     *
     * @param   UserFactoryInterface  $userFactory  The userFactory
     *
     * @since   4.0.0
     */
    public function __construct(UserFactoryInterface $userFactory)
    {
        $this->userFactory = $userFactory;
    }

    /**
     * Method to generate a link to the create item page for the given category
     *
     * @param   object    $category  The category information
     * @param   Registry  $params    The item parameters
     * @param   array     $attribs   Optional attributes for the link
     *
     * @return  string  The HTML markup for the create item link
     *
     * @since  4.0.0
     */
    public function create($category, $params, $attribs = array())
    {
        $uri = Uri::getInstance();

        $url = 'index.php?option=com_ghsthing&task=ghsthing.add&return=' . base64_encode($uri) . '&id=0&catid=' . $category->id;

        $text = '';

        if ($params->get('show_icons')) {
            $text .= '<span class="icon-plus icon-fw" aria-hidden="true"></span>';
        }

        $text .= Text::_('COM_GHSTHING_NEW_GHSTHING');

        // Add the button classes to the attribs array
        if (isset($attribs['class'])) {
            $attribs['class'] .= ' btn btn-primary';
        } else {
            $attribs['class'] = 'btn btn-primary';
        }

        $button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

        return $button;
    }
}
