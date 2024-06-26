<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GHSVS\Component\GhsThing\Administrator\Extension;

use Joomla\CMS\Helper\ContentHelper as LibraryContentHelper;

use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\CMS\Tag\TagServiceTrait;
use Joomla\CMS\User\UserFactoryInterface;
//use Joomla\Component\GhsThing\Administrator\Service\HTML\AdministratorService;
use GHSVS\Component\GhsThing\Administrator\Service\HTML\IconGhsthing;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_contact
 *
 * @since  4.0.0
 */
class GhsthingComponent extends MVCComponent implements
    BootableExtensionInterface,
    CategoryServiceInterface,
    FieldsServiceInterface,
    RouterServiceInterface,
    TagServiceInterface
{
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;
    use CategoryServiceTrait, TagServiceTrait {
        CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
        CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
    }

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface  $container  The container
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {
        //$this->getRegistry()->register('contactadministrator', new AdministratorService());
        $this->getRegistry()->register('ghsthingicon', new IconGhsthing($container->get(UserFactoryInterface::class)));
    }

    /**
     * Returns a valid section for the given section. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     * @param   object  $item     The item
     *
     * @return  string|null  The new section
     *
     * @since   4.0.0
     */
    public function validateSection($section, $item = null)
    {
        if (Factory::getApplication()->isClient('site') && $section == 'contact' && $item instanceof Form) {
            // The contact form needs to be the mail section
            $section = 'mail';
        }

        if (Factory::getApplication()->isClient('site') && ($section === 'category' || $section === 'form')) {
            // The contact form needs to be the mail section
            $section = 'contact';
        }

        if ($section !== 'mail' && $section !== 'contact') {
            // We don't know other sections
            return null;
        }

        return $section;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getContexts(): array
    {
        Factory::getLanguage()->load('com_contact', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_contact.contact'    => Text::_('COM_CONTACT_FIELDS_CONTEXT_CONTACT'),
            'com_contact.mail'       => Text::_('COM_CONTACT_FIELDS_CONTEXT_MAIL'),
            'com_contact.categories' => Text::_('JCATEGORY')
        );

        return $contexts;
    }

    /**
     * Returns the table for the count items functions for the given section.
     *
     * @param   string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getTableNameForSection(string $section = null)
    {
        return ($section === 'category' ? 'categories' : 'contact_details');
    }

    /**
     * Returns the state column for the count items functions for the given section.
     *
     * @param   string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getStateColumnForSection(string $section = null)
    {
        return 'published';
    }

   /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  $items    The category objects
     * @param   string       $section  The section
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function countItems(array $items, string $section)
    {
        $config = (object) [
            'related_tbl'         => 'ghsthing',
            'state_col'           => 'state',
            'group_col'           => 'catid',
            'relation_type'       => 'category_or_group',
            'uses_workflows'      => false,
            'workflows_component' => 'com_ghsthing',
        ];

        LibraryContentHelper::countRelations($items, $config);
    }
}
