<script type="text/template" id="template-product">
    <li class="product col-lg-2 col-md-2 col-sm-2 <%= surging %>" data-product-id="<%= product_id %>">
			<img class="product-icon" src="<%=image%>">
			<div class="time"><%= minutes %><br/> MINUTES</div>
			<div class="capacity"><%= capacity %><br/> PEOPLE </div>
			<h3><%= display_name %></h4>
			<div class="cb"></div>
	</li>
</script>

<script type="text/template" id="template-foursquareFail">
	<li data-venue-id="<%= id %>"><a href="<%= request_url %>" data-venue-id="<%= id %>" class="request-link"> <%= name %> - <%= location_address %>, <%= location_city %> </a></li>
</script>