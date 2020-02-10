import React, {Component} from "react";
//import "bootstrap/dist/css/bootstrap.min.css";

class RegisterForm extends Component {
    constructor(props) {
        super(props);
        this.state = {
            login: '',
            email: '',
            firstName: '',
            lastName: '',
            password: '',
            age: '',
            confirmPassword: '',
            errorMessageLogin: '',
            errorMessageEmail: '',
            errorMessageFirstName: '',
            errorMessageLastName: '',
            errorMessagePswd: '',
            errorMessageCnfrmPswd: '',
            isRegistered: 'false',
        };
    }

    myChangeHandler = (event) => {
        let nam = event.target.name;
        let val = event.target.value;
        this.setState({[nam]: val});
    };

    submitHandler = event => {
        event.preventDefault();
        //if (this.checkFormFields()) {
            this.registerUser(this.state)
                .then(data => this.readServerResponse(data)) // JSON-строка полученная после вызова `response.json()`
                .catch(error => console.error(error));
        //}
    };

    readServerResponse(response) {
      this.setState({errorMessageLogin: response});
    };

    checkLogin(login) {
        return (login && login.length > 2 && login.length < 32);
    }

    checkName(name) {
        return (name.length > 2);
    }

    checkEmail(email) {
        let reg = /[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
        return (email && reg.test(email));
    }

    checkPassword(password, confirmPassword) {
        let hasUpperCase = /[A-Z]/.test(password);
        let hasLowerCase = /[a-z]/.test(password);
        let hasNumbers = /\d/.test(password);
        let hasNonalphas = /\W/.test(password);
        return (password.length >= 6 && (hasUpperCase + hasLowerCase + hasNumbers + hasNonalphas >= 3));
    }

    checkPasswordsEqual(password, confirmPassword) {
        return (password === confirmPassword);
    }

    /*registerUser = () => {
        let xhr = new XMLHttpRequest();
        let body = 'login=' + encodeURIComponent(this.state.login) + '&email=' + encodeURIComponent(this.state.email) + '&password=' + encodeURIComponent(this.state.password)
                    + '&firstName=' + encodeURIComponent(this.state.firstName) + '&lastName=' + encodeURIComponent(this.state.lastName);
        xhr.open("POST", '/account/register', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send(body);
        xhr.onreadystatechange = function() {
            if (this.readyState !== 4) return;
            console.log(this.responseText);
        };

        function readServerResponse(response) {
            return response[0];
        }
    };*/

    registerUser(state) {
    // Значения по умолчанию обозначены знаком *
    return fetch('/account/register', {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        mode: 'cors', // no-cors, cors, *same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            //'Content-Type': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: 'follow', // manual, *follow, error
        referrer: 'no-referrer', // no-referrer, *client
        body: 'login=' + encodeURIComponent(this.state.login) + '&email=' + encodeURIComponent(this.state.email) + '&firstName=' + encodeURIComponent(this.state.firstName)
            + '&lastName=' + encodeURIComponent(this.state.lastName) + '&password=' + encodeURIComponent(this.state.password) + '&age=' + encodeURIComponent(this.state.age),
        })
        .then(response => response.text()); // парсит JSON ответ в Javascript объект
}

    checkFormFields = () => {
        if (this.checkLogin(this.state.login)) {
            if (this.checkEmail(this.state.email)) {
                if (this.checkPassword(this.state.password)) {
                    if (this.checkName(this.state.firstName)) {
                        if (this.checkName(this.state.lastName)) {
                            if (this.checkPasswordsEqual(this.state.password, this.state.confirmPassword)) {
                                return true;
                            }
                            else {
                                this.setState({errorMessageCnfrmPswd: "Passwords are not equal."});
                            }
                        }
                        else {
                            this.setState({errorMessageLastName: "Last name too short"});
                        }
                    }
                    else {
                        this.setState({errorMessageFirstName: "First name too short"});
                    }
                }
                else {
                    this.setState({errorMessagePswd: "Password must contain minimum 6 latin characters with at least one capital letter and one digit"});
                }
            }
            else {
                this.setState({errorMessageEmail: "E-mail address is invalid."});
            }
        }
        else {
            this.setState({errorMessageLogin: "Login must contain between 3 and 32 characters."});
        }
        return false;
    };

    render() {
        return (
            <div className="container text-center">
                <form onSubmit={this.submitHandler} className="w-25 mx-auto mt-5">
                    <div className="form-group">
                        <label htmlFor="InputLogin">Login</label>
                        <input
                            type='text'
                            className="form-control"
                            id="InputLogin"
                            name="login"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageLogin: ''})}
                        />
                        {this.state.errorMessageLogin && <small id="nameHelp" className="form-text text-danger">{this.state.errorMessageLogin}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="InputEmail">E-mail</label>
                        <input
                            type='text'
                            className="form-control"
                            id="InputEmail"
                            name="email"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageEmail: ''})}
                        />
                        {this.state.errorMessageEmail && <small id="nameHelp" className="form-text text-danger">{this.state.errorMessageEmail}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="InputFirstName">First Name</label>
                        <input
                            type='text'
                            className="form-control"
                            id="InputFirstName"
                            name="firstName"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageEmail: ''})}
                        />
                        {this.state.errorMessageFirstName && <small id="nameHelp" className="form-text text-danger">{this.state.errorMessageFirstName}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="InputLastName">LastName</label>
                        <input
                            type='text'
                            className="form-control"
                            id="InputLastName"
                            name="lastName"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageEmail: ''})}
                        />
                        {this.state.errorMessageLastName && <small id="nameHelp" className="form-text text-danger">{this.state.errorMessageLastName}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="InputPasswd">Password</label>
                        <input
                            type='password'
                            className="form-control"
                            id="InputPasswd"
                            name="password"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessagePswd: ''})}
                        />
                        {this.state.errorMessagePswd && <small id="pswdHelp" className="form-text text-danger">{this.state.errorMessagePswd}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="ConfirmPasswd">Confirm Password</label>
                        <input
                            type='password'
                            className="form-control"
                            id="ConfirmPasswd"
                            name="confirmPassword"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageCnfrmPswd: ''})}
                        />
                        {this.state.errorMessageCnfrmPswd && <small id="pswdHelp" className="form-text text-danger">{this.state.errorMessageCnfrmPswd}</small>}
                    </div>
                    <div className="form-group">
                        <label htmlFor="age">Age</label>
                        <input
                            type='text'
                            className="form-control"
                            id="age"
                            name="age"
                            onChange={this.myChangeHandler}
                            onFocus={() => this.setState({errorMessageCnfrmPswd: ''})}
                        />
                        {this.state.errorMessageCnfrmPswd && <small id="pswdHelp" className="form-text text-danger">{this.state.errorMessageCnfrmPswd}</small>}
                    </div>
                    <button type="submit" className="btn btn-primary">Register</button>
                </form>
                <p><a href="/account/login">Login</a> / <a href="/account/newpass">Forgot your password?</a></p>
            </div>
        );
    }
}

export default RegisterForm;