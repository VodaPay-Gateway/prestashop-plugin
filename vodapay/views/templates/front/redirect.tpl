
<div>
	<h3>{l s='Redirect your customer' mod='vodapay'}:</h3>
	<ul class="alert alert-info">
			<li>{l s='This action should be used to redirect your customer to the website of your payment processor' mod='vodapay'}.</li>
	</ul>

	<div class="alert alert-warning">
		{l s='You can redirect your customer with an error message' mod='vodapay'}:
		<a href="{$link->getModuleLink('vodapay', 'redirect', ['action' => 'error'], true)|escape:'htmlall':'UTF-8'}" title="{l s='Look at the error' mod='vodapay'}">
			<strong>{l s='Look at the error message' mod='vodapay'}</strong>
		</a>
	</div>

	<div class="alert alert-success">
		{l s='You can also redirect your customer to the confirmation page' mod='vodapay'}:
		<a href="{$link->getModuleLink('vodapay', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}" title="{l s='Confirm' mod='vodapay'}">
			<strong>{l s='Go to the confirmation page' mod='vodapay'}</strong>
		</a>
	</div>
</div>
