<div class="row">
  <div class="col-md-3">


		<ul id="emAccountsTree" class="nav nav-pills nav-stacked">
			
			
			
				

    <script type="text/html" id="emAccountsTreeTpl">
			
			
			
			
      <% for ( var i = 0; i < accounts.length; i++ ) { %>
        <li><a droppable ondrop="function(){}" data-load-to="" onclick="GO.email.loadMessages(<%=accounts[i].account_id%>);" href="#<%=accounts[i].account_id%>"><%=accounts[i].name%></a></li>
      <% } %>
    </script>




			
<!--			<li class="active">
				<a href="#">
					<span class="badge pull-right">42</span>
					mschering@intermesh.nl
				</a>
			</li>
			<li>
				<a href="#">Drafts</a>
			</li>
			<li>
				<a href="#">Sent items</a>
			</li>
			<li>
				<a href="#">Trash</a>
			</li>-->

		</ul>


	</div>
  <div class="col-md-3">
		
		
		<script type="text/html" id="emAccountsTreeTpl">
      <% for ( var i = 0; i < accounts.length; i++ ) { %>
        <li><a data-load-to=""  draggable onclick="GO.email.loadMessages(<%=accounts[i].account_id%>);" href="#<%=accounts[i].account_id%>"><%=accounts[i].name%></a></li>
      <% } %>
    </script>


		<div id="messages" data-on-delete="" data-on-select="" data-tpl="emAccountsTreeTpl" data-url="email/message/store" class="list-group">
<!--      <a href="#" class="list-group-item active">
        <h4 class="list-group-item-heading">List group item heading</h4>
        <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
      </a>
      <a href="#" class="list-group-item">
        <h4 class="list-group-item-heading">List group item heading</h4>
        <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
      </a>
      <a href="#" class="list-group-item">
        <h4 class="list-group-item-heading">List group item heading</h4>
        <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
      </a>-->
    </div>


	</div>
  <div class="col-md-6">

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Example e-mail subject</h3>
			</div>
			<div class="panel-body">
				
			
Merijn,<br><br>bedankt voor de prijzen, maar vindt thinkfree erg duur. Vorige keer zij je dat jou inkoop 1 euro was.<br><br>Was wij er ook niets moeten verdienen dan wordt het te duur.<br><br>Verneem graag je reactie<br><br><br><br>Met vriendelijke groet,<br>Han van Eijden<br><br><a target="_blank" class="blue" href="http://www.firmtel.nl/crm-integratie-maar-dan-echt/">Work Anywhere</a> met Group-office relatiebeheer en <a target="_blank" class="blue" href="http://www.firmtel.nl/persbericht-firmtel-wint-telecom-inspirience-award-2013/">award winning</a> Firmel vast mobiel integratie.<br>&nbsp;<br>


				
				
				
			</div>
		</div>

	</div>
</div>

<?php
$script = <<<END

	var tree = $('#emAccountsTree');
	
	$.ajax({
			url:GO.url("email/account/tree"), 
			dataType:'json',
			data:{
				node:'root'
			},
			success: function(data, textStatus, jqXHR){
				console.log(data);
				var html = GO.tmpl("emAccountsTreeTpl", {accounts: data});
					console.log(html);
					$('#emAccountsTree').html(html);
			}
		});
END;

GO::scripts()->registerScript('submit', $script);
?>