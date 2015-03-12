{JS src/Molotov/Modules/Auth/Web/js/passwordResetRequest.js}
{CSS web/css/bootstrap.min.css, src/Molotov/Modules/Auth/Web/css/custom.css }
<div id="passwordResetRequest" class="container" style="display: none;" data-bind="visible: show, css: { loading: loading}">
    <div  class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4" data-bind="if:show">
            <div class="account-wall">
            	<h1 class="text-center login-title">Please enter the email your account is registered under.</h1>
                <form class="form-signin" data-bind="submit: PasswordResetRequest">
				<div class="alert alert-danger" data-bind="style: { display: resp() ? 'block' : 'none'} " style="display: none;" >
					<div class="header" data-bind="text: resp"></div>
				</div>
				<div class="alert alert-danger" data-bind="style: { display: error() ? 'block' : 'none'} " style="display: none;" >
					<div class="header" data-bind="text: error"></div>
				</div>
                <input type="password" class="form-control" placeholder="Email" required autofocus data-bind="value: email">

                <button class="btn btn-lg btn-primary btn-block" type="submit" data-bind="click: PasswordResetRequest">Request Reset</button>
                </form>
            </div>
            <a href="#login" class="text-center new-account">Never mind, Login </a>
        </div>
    </div>
</div><!-- /end passwordResetRequest -->