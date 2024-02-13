<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace WebApp\Core\ExtendedController;

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to edit data.
 * It can be composed of several views.
 * The main view is put on top and the rest are shown in tabs.
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class EditController extends BaseController
{
    /**
     * Indicates if the main view has data or is empty.
     *
     * @var bool
     */
    public bool $hasData = false;

    /**
     * Initializes all the objects and properties.
     *
     * @param string $className
     * @param string $uri
     */
    public function __construct(string $className, string $uri = '')
    {
        parent::__construct($className, $uri);
        $this->setTemplate('Master/EditController');
    }

    /**
     * Runs the controller's logic.
     * if return false, the controller break the execution.
     *
     * @param Response $response
     * @return bool
     */
    public function exec(Response &$response): bool
    {
        if (false === parent::exec($response)) {
            return false;
        }

        // Get action and execute if not empty
        $action = $this->request->request->get('action', $this->request->query->get('action', ''));
        if (false === $this->execPreviousAction($action)) {
            return false;
        }

        // Load the data for each view
        $mainViewName = $this->getMainViewName();
        foreach ($this->views as $viewName => $view) {
            // disable views if main view has no data
            if ($viewName != $mainViewName && false === $this->hasData) {
                $this->setSettings($viewName, 'active', false);
            }

            // exclude inactive views
            if (false === $view->settings['active']) {
                continue;
            }

            $case = $this->active == $viewName ? 'load' : 'preload';
            $view->processFormData($this->request, $case);
            $this->loadData($viewName, $view);

            if ($viewName === $mainViewName && $view->model->exists()) {
                $this->hasData = true;
            }
        }

        // Execute actions after loading data
        $this->execAfterAction($action);
        return true;
    }

    /**
     * Adds a EditList type view to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addEditListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-bars'): void
    {
        $view = new EditListView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds an Edit type view to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addEditView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-edit'): void
    {
        $view = new EditView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds a List type view to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-list'): void
    {
        $view = new ListView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds an HTML type view to the controller.
     *
     * @param string $viewName
     * @param string $fileName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addHtmlView(string $viewName, string $fileName, string $modelName, string $viewTitle, string $viewIcon = 'fab fa-html5')
    {
        $view = new HtmlView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $fileName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Runs the data edit action.
     *
     * @return bool
     */
    protected function editAction(): bool
    {
       if (false === $this->validateFormToken()) {
            return false;
        }

        // loads model data
        $code = $this->request->request->get('code', '');
        if (false === $this->views[$this->active]->model->loadFromCode($code)) {
            return false;
        }

        // loads form data
        $this->views[$this->active]->processFormData($this->request, 'edit');

        // save in database
        if ($this->views[$this->active]->model->save()) {
            $this->message->info('Registro actualizado correctamente.');
            return true;
        }

        $this->message->error('Error guardando los datos.');
        return false;
    }

    /**
     * Run the controller after actions.
     *
     * @param string $action
     */
    protected function execAfterAction(string $action): void
    {
        switch ($action) {
            case 'save-ok':
                $this->message->info('Registro guardado correctamente.');
                break;
        }
    }

    /**
     * Run the actions that alter data before reading it.
     *
     * @param ?string $action
     * @return bool
     */
    protected function execPreviousAction(?string $action): bool
    {
        switch ($action) {
            case 'autocomplete':
                $this->setTemplate(false);
                $results = $this->autocompleteAction();
                $this->response->setContent(json_encode($results));
                return false;

            case 'delete':
                if ($this->deleteAction() && $this->active === $this->getMainViewName()) {
                    // al eliminar el registro principal, redirigimos al listado para mostrar ahí el mensaje de éxito
                    $listUrl = $this->views[$this->active]->model->url('list');
                    $redirect = str_contains($listUrl, '?')
                        ? $listUrl . '&action=delete-ok'
                        : $listUrl . '?action=delete-ok';
                    $this->redirect($redirect);
                }
                break;

            case 'edit':
                if ($this->editAction()) {
                    $this->views[$this->active]->model->clear();
                }
                break;

            case 'insert':
                if ($this->insertAction() || !empty($this->views[$this->active]->model->primaryColumnValue())) {
                    $this->views[$this->active]->model->clear();   // we need to clear model in these scenarios
                }
                break;

            case 'select':
                $this->setTemplate(false);
                $results = $this->selectAction();
                $this->response->setContent(json_encode($results));
                return false;
        }

        return true;
    }

    /**
     * Runs data insert action.
     *
     * @return bool
     */
    protected function insertAction(): bool
    {
        if (false === $this->validateFormToken()) {
            return false;
        }

        // loads form data
        $this->views[$this->active]->processFormData($this->request, 'edit');
        if ($this->views[$this->active]->model->exists()) {
            $this->message->error('Registro duplicado.');
            return false;
        }

        // save in database
        if (false === $this->views[$this->active]->model->save()) {
            $this->message->error('Error guardando los datos.');
            return false;
        }

        // redirect to new model url only if this is the first view
        if ($this->active === $this->getMainViewName()) {
            $this->redirect($this->views[$this->active]->model->url('edit') . '&action=save-ok');
        }

        $this->views[$this->active]->newCode = $this->views[$this->active]->model->primaryColumnValue();
        $this->message->info('Registro guardado correctamente.');
        return true;
    }
}
