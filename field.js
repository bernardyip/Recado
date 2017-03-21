function validateForm() {
	var valid = true;
	if (!validateMandatoryFields()) valid = false;
	if (!passwordsMatch()) valid = false;
	if (!validateEmail()) valid = false;
	if (!validatePhoneNumber()) valid = false;

	return valid;
}

function validateMandatoryFields() {
	var valid = true;
    var username = document.getElementsByName("username")[0].value;
    var usernameInvalid = username == null || !(/\S/.test(username));
    var name = document.getElementsByName("name")[0].value;
    var nameInvalid = name == null || !(/\S/.test(name));

    if (usernameInvalid) {
        document.getElementsByName("requiredUsername")[0].style.display = "block";
        document.getElementsByName("username")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredUsername")[0].style.display = "none";
        document.getElementsByName("username")[0].style.borderColor = "initial";
    }

    if (nameInvalid) {
        document.getElementsByName("requiredName")[0].style.display = "block";
        document.getElementsByName("name")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredName")[0].style.display = "none";
        document.getElementsByName("name")[0].style.borderColor = "initial";
    }

    return valid;
}

function validateEmail() {
    var email = document.getElementsByName("email")[0].value;
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	var valid = regex.test(email);
	if (valid) {
        document.getElementsByName("badEmail")[0].style.display = "none";
        document.getElementsByName("email")[0].style.borderColor = "initial";
	} else {
        document.getElementsByName("badEmail")[0].style.display = "block";
        document.getElementsByName("email")[0].style.borderColor = "#E34234";
	}
    return valid; 
}

function validatePhoneNumber() {
    var phoneNumber = document.getElementsByName("phone")[0].value;
	var regex = /^[0-9]{8}$/;
    var valid = regex.test(phoneNumber);
    if(valid) {
        document.getElementsByName("badPhone")[0].style.display = "none";
        document.getElementsByName("phone")[0].style.borderColor = "initial";
	}  
	else {
        document.getElementsByName("badPhone")[0].style.display = "block";
        document.getElementsByName("phone")[0].style.borderColor = "#E34234";
	}
	return valid;
}
	
function passwordsMatch() {
    var password = document.getElementsByName("password")[0].value;
    var confirmPassword = document.getElementsByName("confirmPassword")[0].value;
    var passwordInvalid = password == null || !(/\S/.test(password));
    if (passwordInvalid) {
        document.getElementsByName("requiredPassword")[0].style.display = "block";
        document.getElementsByName("password")[0].style.borderColor = "#E34234";
        document.getElementsByName("mismatchPassword")[0].style.display = "none";
        document.getElementsByName("confirmPassword")[0].style.borderColor = "initial";
        return false;
    } else {
        document.getElementsByName("requiredPassword")[0].style.display = "none";
        document.getElementsByName("password")[0].style.borderColor = "initial";
        if (password != confirmPassword) {
            document.getElementsByName("mismatchPassword")[0].style.display = "block";
            document.getElementsByName("password")[0].style.borderColor = "#E34234";
            document.getElementsByName("confirmPassword")[0].style.borderColor = "#E34234";
            return false;
        }
        else {
            document.getElementsByName("mismatchPassword")[0].style.display = "none";
            document.getElementsByName("password")[0].style.borderColor = "initial";
            document.getElementsByName("confirmPassword")[0].style.borderColor = "initial";
    		return true;
        }
    }
}