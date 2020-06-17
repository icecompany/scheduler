<?php
/**
 * @package    scheduler
 *
 * @author     anton@nazvezde.ru <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Scheduler Controller.
 *
 * @package  scheduler
 * @since    1.0.0
 */
class SchedulerController extends BaseController
{
	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $default_view = 'tasks';

	public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->getString('view');
        if ($view === 'schedulers') {
            $this->setRedirect("index.php?option=com_scheduler&view=tasks");
            $this->redirect();
            jexit();
        }
        return parent::display($cachable, $urlparams);
    }
}
