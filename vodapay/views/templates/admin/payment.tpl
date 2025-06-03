
{if $authorizedOrder }
    <div id="formAddPaymentPanel" class="card mt-2 d-print-none panel col-sm-12">
        <div class="panel-heading">
            <i class="icon-money"></i><h3> {$moduleDisplayName|escape:'htmlall':'UTF-8'}</h3><span class="badge"></span>
        </div>

            <div class="well col-sm-12">
                <div class="col-sm-6">
                    <div class="panel-heading">Capture / Void<span class="badge"></span></div>
                    Authorized Amount : {$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'} {$vodaPayOrder['amount']|escape:'htmlall':'UTF-8'}<br/>  <br>
                    <div class="col-sm-6">
                        <form class="container-command-top-spacing" action="{$formAction|escape:'htmlall':'UTF-8'}" method="post")>
                            <button type="submit" name="fullyCaptureVodaPay" class="btn btn-primary mb-3">
                                <i class="icon-check"></i> Full Capture
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6">
                        <form class="container-command-top-spacing" action="{$formAction|escape:'htmlall':'UTF-8'}" method="post")>
                            <button type="submit" name="voidVodaPay" class="btn btn-primary mb-3">
                                <i class="icon-check"></i> Void
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel-heading"> Payment Information <span class="badge"></span></div>
                    <span>Payment Reference :  {$vodaPayOrder['reference']|escape:'htmlall':'UTF-8'}</span></br></br>
                    <span>Payment Id : {$vodaPayOrder['id_payment']|escape:'htmlall':'UTF-8'}</span></br></br>
                </div>
            </div>

    </div>
{else}

<div id="formAddPaymentPanel" class="card mt-2 d-print-none panel col-sm-12">
    <div class="panel-heading">
        <i class="icon-money"></i><h3> {$moduleDisplayName|escape:'htmlall':'UTF-8'}</h3><span class="badge"></span>
    </div>
    <div class="well col-sm-12">
        <div class="col-sm-4">
            <div class="panel-heading"><b>Refund<span class="badge"></b></span></div>
            <form class="container-command-top-spacing" action="{$formAction|escape:'htmlall':'UTF-8'}" method="post")>
                Captured Amount : {$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'} {$vodaPayOrder['capture_amt']|escape:'htmlall':'UTF-8'}<br/>  <br>
                <div class="input-group" id="refund-input">Amount to refund: <input type="number" name="refundAmount" step="any" required  min="0.01">{$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'}</div><br>
                <button type="submit" name="partialRefundVodaPay" class="btn btn-primary mb-3" id="refund-button">
                    <i class="icon-check"></i> Refund
                </button>
            </form>
        </div>


            <div class="col-sm-8">
                <div class="panel-heading"> Payment Information <span class="badge"></span></div>
                <div class="col-sm-8">
                    <span>Payment Reference : {$vodaPayOrder['reference']|escape:'htmlall':'UTF-8'}</span></br></br>
                    <span>Payment Id        : {$vodaPayOrder['id_payment']|escape:'htmlall':'UTF-8'}</span></br></br>
                    <span>Capture Id       : {$vodaPayOrder['id_capture']|escape:'htmlall':'UTF-8'}</span></br></br>
                </div>
                <div class="col-sm-4 ">
                    <span>Total Paid        : {$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'} {$vodaPayOrder['amount']|escape:'htmlall':'UTF-8'}</span></br></br>
                    <span>Total Capture     : {$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'} {$vodaPayOrder['capture_amt']|escape:'htmlall':'UTF-8'}</span></br></br>
                    <span>Total Refunded    : {$vodaPayOrder['currency']|escape:'htmlall':'UTF-8'} {$vodaPayOrder['refunded_amt']|escape:'htmlall':'UTF-8'}</span></br></br>
                </div>
            </div>

    </div>
</div>

{/if}

<script>
  // Assuming $hideButton is the JavaScript variable passed to the .tpl file
  var hideRefundBtn = {$hideRefundBtn};

  // Function to hide or show the button based on the value of hideButton
  function toggleButtonVisibility() {
    var button = document.getElementById('refund-button')
    var refundInput = document.getElementById('refund-input')
    if (hideRefundBtn) {
      button.style.display = 'none' // Hide the button
      refundInput.style.display = 'none'
    } else {
      button.style.display = 'block' // Show the button
      refundInput.style.display = 'block'
    }
  }

  // Call the function to initially set the button visibility
  toggleButtonVisibility()
</script>
