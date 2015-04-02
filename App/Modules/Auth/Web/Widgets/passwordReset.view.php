{JS App/Modules/Auth/Web/js/passwordReset.js}
{CSS web/css/bootstrap.min.css, App/Modules/Auth/Web/css/custom.css }
<div id="passwordReset" class="container" style="display: none;" data-bind="visible: show, css: { loading: loading}">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4" data-bind="if:show">
            <div class="account-wall">
            	<h1 class="text-center login-title">Please choose new password</h1>
                <form class="form-signin" data-bind="submit: PasswordReset">
				<div class="alert alert-danger" data-bind="style: { display: resp() ? 'block' : 'none'} " style="display: none;" >
					<div class="header" data-bind="text: resp"></div>
				</div>
				<div class="alert alert-danger" data-bind="style: { display: error() ? 'block' : 'none'} " style="display: none;" >
					<div class="header" data-bind="text: error"></div>
				</div>
                <input type="password" class="form-control" placeholder="Password" required autofocus data-bind="value: password">
                <input type="password" class="form-control" placeholder="Password Confirmation" required data-bind="value: password_confirmation">

                <button class="btn btn-lg btn-primary btn-block" type="submit" data-bind="click: PasswordReset">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div><!-- /end passwordReset -->