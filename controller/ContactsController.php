<?php

//use Contact;

require_once 'model/ContactsService.php';

class ContactsController {

    private $contactsService = NULL;

    public function __construct() {
        $this->contactsService = new ContactsService();
    }

    public function redirect($location) {
        header('Location: ' . $location);
    }

    public function handleRequest() {
        $op = isset($_GET['op']) ? $_GET['op'] : NULL;
        try {
            if (!$op || $op == 'list') {
                $this->listContacts();
            } elseif ($op == 'new') {
                $this->saveContact();
            } elseif ($op == 'edit') {
                $this->editContact();
            } elseif ($op == 'delete') {
                $this->deleteContact();
            } elseif ($op == 'show') {
                $this->showContact();
            } elseif ($op == 'showFull') {
                $this->showContactFull();
            } else {
                $this->showError("Page not found", "Page for operation " . $op . " was not found!");
            }
        } catch (Exception $e) {
            // some unknown Exception got through here, use application error page to display it
            $this->showError("Application error", $e->getMessage());
            throw $e;
        }
    }

    public function listContacts() {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : NULL;
        $contacts = $this->contactsService->getAllContacts($orderby);
        include 'view/contacts.php';
    }

    public function saveContact() {

        $title = 'Add new contact';

        $name = '';
        $phone = '';
        $email = '';
        $address = '';

        $errors = array();

        if (isset($_POST['form-submitted'])) {

            $name = isset($_POST['name']) ? $_POST['name'] : NULL;
            $phone = isset($_POST['phone']) ? $_POST['phone'] : NULL;
            $email = isset($_POST['email']) ? $_POST['email'] : NULL;
            $address = isset($_POST['address']) ? $_POST['address'] : NULL;

            try {
                $this->contactsService->createNewContact($name, $phone, $email, $address);
                $this->redirect('index.php');
                return;
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        include 'view/contact-form.php';
    }

    public function editContact() {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        if (!$id) {
            throw new Exception('Internal error.');
        }
        $contact = $this->contactsService->getContact($id);

        if (isset($_POST['form-submitted'])) {
            $name = isset($_POST['name']) ? $_POST['name'] : NULL;
            $phone = isset($_POST['phone']) ? $_POST['phone'] : NULL;
            $email = isset($_POST['email']) ? $_POST['email'] : NULL;
            $address = isset($_POST['address']) ? $_POST['address'] : NULL;

            try {
                $this->contactsService->updateContact($id, $name, $phone, $email, $address);
                $this->redirect('index.php?op=show&id=' . $id);
                return;
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        include 'view/contact_edit.php';
    }

    public function deleteContact() {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        if (!$id) {
            throw new Exception('Internal error.');
        }

        $this->contactsService->deleteContact($id);

        $this->redirect('index.php');
    }

    public function showContact() {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        if (!$id) {
            throw new Exception('Internal error.');
        }
        $contact = $this->contactsService->getContact($id);

        include 'view/contact.php';
    }

    public function showContactFull() {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        if (!$id) {
            throw new Exception('Internal error.');
        }
        $name = isset($_GET['name']) ? $_GET['name'] : NULL;
        $phone = isset($_GET['phone']) ? $_GET['phone'] : NULL;
        $email = isset($_GET['email']) ? $_GET['email'] : NULL;
        $address = isset($_GET['address']) ? $_GET['address'] : NULL;

        $contact = new Contact();
        $contact->setId($id);
        $contact->setName($name);
        $contact->setPhone($phone);
        $contact->setEmail($email);
        $contact->setAddress($address);

        include 'view/contact.php';
    }

    public function showError($title, $message) {
        include 'view/error.php';
    }

}

?>
