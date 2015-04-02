{JS App/Modules/Auth/Web/js/signup.js}
{CSS web/css/bootstrap.min.css, App/Modules/Auth/Web/css/custom.css }
<div class="container">
    <div id="signup" class="row" style="display: none;" data-bind="visible: show, css: { loading: loading}">
        <div class="col-sm-6 col-md-4 col-md-offset-4" data-bind="if:show">
            <div class="account-wall">
            	<h1 class="text-center login-title">Create an account</h1>
                <form class="form-signin" data-bind="submit: signup, visible: !complete()">
				<div class="alert alert-danger" style="display: none" data-bind="visible: error">
					<div class="header">Error</div>
					<p data-bind="html: error"></p>
				</div>
                <input type="text" class="form-control" placeholder="Display Name" required autofocus data-bind="value: display_name">
                <input type="text" class="form-control" placeholder="Email" required autofocus data-bind="value: email">
                <input type="password" class="form-control" placeholder="Password" required data-bind="value: password">
                <button class="btn btn-lg btn-primary btn-block" type="submit" data-bind="click: signup">
                    Sign Up</button>
                <a href="#login" class="pull-right need-help">or Login</a><span class="clearfix"></span>
                </form>
				<div style="display: none" data-bind="visible: complete">
					<p>Thank you, Now check your email for activation confirmation.</p>
				</div>
            </div>
        </div>
    </div>
	<div id="SignupActivation" class="row" style="display: none;" data-bind="visible: show, css: { loading: loading}">
		<p>Thank you for signing up, you may now login.</p>
		<a href="#login">Login</a>
	</div>
</div>