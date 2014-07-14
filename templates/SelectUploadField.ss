<% if $FolderSelector %><% with FolderSelector %>
	<div class="field $extraClass">
		<% if $Title %><label for="$ID">$Title</label><% end_if %>
		$Field
	</div>
<% end_with %><% end_if %>
<% include UploadField %>
