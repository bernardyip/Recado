<?php 

include_once 'data/UserDatabase.php';
include_once 'HtmlHelper.php';

session_start();

class ProfileModel {
	public $username;
	public $password;
    public $email;
    public $phone;
    public $name;
    public $bio;
    public $profileEditSuccess = false;
    public $message;
    
    
    public function __construct() {
    }
    
    public function isValid() {
        if (is_null($this->password)) return false;
        if (is_null($this->email)) return false;
        if (is_null($this->phone)) return false;
        if (is_null($this->name)) return false;
        if (is_null($this->bio)) return false;
        
        return strlen($this->password) > 0 &&
        strlen($this->email) > 0 &&
        strlen($this->phone) > 0 &&
        strlen($this->name) > 0 &&
        strlen($this->bio) > 0;
    }
}

class ProfileView {
    private $model;
    private $controller;
    
    public function __construct($controller, $model) {
        $this->controller = $controller;
        $this->model = $model;
    }

    public function getPasswordField() {
        return HtmlHelper::makeInput( "password", "password", "", "", "");
    }
    
    public function getConfirmPasswordField() {
        return HtmlHelper::makeInput( "password", "confirmPassword", "", "", "");
    }
    
    public function getNameField() {
        return HtmlHelper::makeInput( "text", "name", htmlspecialchars($this->model->name), "", "");
    }
    
    public function getEmailField() {
        return HtmlHelper::makeInput( "email", "email", htmlspecialchars($this->model->email), "", "");
    }
    
    public function getPhoneField() {
        return HtmlHelper::makeInput( "tel", "phone", htmlspecialchars($this->model->phone), "", "");
    }
    
    public function getBioField() {
        return HtmlHelper::makeInput( "text", "bio", htmlspecialchars($this->model->bio), "", "");
    }
}

class ProfileController {
    const PROFILE_URL = "/profile.php?message=";
    const PROFILE_METHOD = "POST";
    const PROFILE_URL = "/login.php";
    
    const PROFILE_UPDATE_SUCCESS = "Updated user successfully.";
    const PROFILE_UPDATE_FAIL = "Incorrect password.";
    
    private $model;
    private $userDatabase;
    
    public function __construct($model) {
        $this->model = $model;
        $this->userDatabase = new UserDatabase ();
    }
    
    public function register() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $name = pg_escape_string ( $_POST ['name'] );
        $bio = pg_escape_string ( $_POST ['bio'] );
        
        $result = $this->userDatabase->update( $this->model->username, $this->model->password, $this->model->name, $this->model->bio );
        if ($result->status === UserDatabaseResult::REGISTER_SUCCESS) {
            $user = $result->user;
            
            $this->model->username = $user->username;
            $this->model->password = $password;
            $this->model->message = PROFILE_UPDATE_SUCCESS;
            $this->model->profileEditSuccess = true;
            
            header ( "Refresh: 3; URL=" . ProfileController::PROFILE_URL . "?username=" . urlencode($user->username) );
        } else {
            if ($result->status === UserDatabaseResult::REGISTER_USERNAME_TAKEN) {
                $this->model->message = PROFILE_UPDATE_FAIL;
            } else if ($result->status === UserDatabaseResult::REGISTER_FAILED) {
                $this->model->message = "An unexpected error has occured, please try again later.";
            }
            $this->model->username = $username;
            $this->model->password = $password;
            $this->model->name = $name;
            $this->model->bio = $bio;
            $this->model->profileEditSuccess = false;
        }
    }
    
    public function handleHttpPost() {
        
        if (isset ( $_POST ['username'] )) {
            $this->model->username = $_POST ['username'];
        }
        if (isset ( $_POST ['password'] )) {
            $this->model->password = $_POST ['password'];
        }
        if (isset ( $_POST ['email'] )) {
            $this->model->email = $_POST ['email'];
        }
        if (isset ( $_POST ['phone'] )) {
            $this->model->phone = $_POST ['phone'];
        }
        if (isset ( $_POST ['name'] )) {
            $this->model->name = $_POST ['name'];
        }
        if (isset ( $_POST ['bio'] )) {
            $this->model->bio = $_POST ['bio'];
        }
        
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'update') {
                $this->register ();
            }
        } else {
            // invalid request.
            http_response_code ( 400 );
            die ();
        }
    }
    
    public function handleHttpGet() {
        if (isset ( $_GET ['action'] )) {
            // request for controller action
            // No controller actions for HTTP GET
        } else {
            // display login page
        }
    }
    
    public function redirectToHome() {
        header ( "Refresh: 0; URL=" . ProfileController::HOME_URL );
        die ();
    }
}

$model = new ProfileModel ();
$controller = new ProfileController ( $model );
$view = new ProfileView ( $controller, $model );

if (isset ( $_SESSION ['username'] )) {
    $controller->redirectToHome ();
}

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
}

?>
