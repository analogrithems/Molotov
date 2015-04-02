{JS App/Modules/Auth/Web/js/login.js}
{CSS web/css/bootstrap.min.css, App/Modules/Auth/Web/css/custom.css }
<div id="login" class="container" style="display: none;" data-bind="visible: show">
    <div class="row" data-bind="if: show">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
            
            <div class="account-wall">
            	<h1 class="text-center login-title">Sign in</h1>
                <img class="profile-img" src="https://lh5.googleusercontent.com/-b0-k99FZlyE/AAAAAAAAAAI/AAAAAAAAAAA/eu7opA4byxI/photo.jpg?sz=120"
                    alt="">
				<div class="alert alert-danger" data-bind="style: { display: error() ? 'block' : 'none'} " style="display: none;" >
					<div class="header" data-bind="text: error"></div>
				</div>
                <form class="form-signin" data-bind="submit: login">
                <input type="text" class="form-control" placeholder="Email" required autofocus data-bind="value: email">
                <input type="password" class="form-control" placeholder="Password" required data-bind="value: password">
                <button class="btn btn-lg btn-primary btn-block" type="submit" data-bind="click: login">
                    Sign in</button>
                <label class="checkbox pull-left">
                    <input type="checkbox" value="remember-me">
                    Remember me
                </label>
                <a href="#passwordResetRequest" class="pull-right need-help">Need help? </a><span class="clearfix"></span>
                </form>
            </div>
            <a href="#signup" class="text-center new-account">Create an account </a>
        </div>
    </div>
</div>