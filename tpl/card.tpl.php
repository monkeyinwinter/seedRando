<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	
	<table width="100%" class="border">
		<tbody>
			<tr class="ref">
				<td width="25%">[langs.transnoentities(Ref)]</td>
				<td>[view.showRef;strconv=no]</td>
			</tr>

			<tr class="label">
				<td width="25%">[langs.transnoentities(Label)]</td>
				<td>[view.showLabel;strconv=no]</td>
			</tr>

			<tr class="distance">
				<td width="25%">[langs.transnoentities(Distance)]</td>
				<td>[view.showDistance;strconv=no]km</td>
			</tr>

			<tr class="difficulte">
				<td width="25%">[langs.transnoentities(Difficulte)]</td>
				<td>[view.showDifficulte;strconv=no]</td>
			</tr>

			<tr class="showWayPoint">
				<td width="25%">[langs.transnoentities(wayPoint)]</td>
				<td>[view.showWayPoint;strconv=no]</td>
			</tr>

			<tr class="status">
				<td width="25%">[langs.transnoentities(Status)]</td>
				<td>[object.getLibStatut(1);strconv=no]</td>
			</tr>
		</tbody>
	</table>
</div> <!-- Fin div de la fonction dol_fiche_head() -->





[onshow;block=begin;when [view.mode]='edit']
<div class="center">
	
	<!-- '+-' est l'équivalent d'un signe '>' (TBS oblige) -->
	[onshow;block=begin;when [object.id]+-0]
	<input type='hidden' name='id' value='[object.id]' />
	
	<input type="submit" value="[langs.transnoentities(Save)]" class="button" />
	[onshow;block=end]
	
	[onshow;block=begin;when [object.id]=0]
	<input type="submit" value="[langs.transnoentities(CreateDraft)]" class="button" />
	[onshow;block=end]
	
	<input type="button" onclick="javascript:history.go(-1)" value="[langs.transnoentities(Cancel)]" class="button">
	
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='edit']
<div class="tabsAction">
	[onshow;block=begin;when [user.rights.seedrando.write;noerr]=1]
	
		[onshow;block=begin;when [object.status]=[seedrando.STATUS_DRAFT]]
			
			<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.id]&action=validate" class="butAction">[langs.transnoentities(Validate)]</a></div>
			<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.id]&action=edit" class="butAction">[langs.transnoentities(Modify)]</a></div>
			
		[onshow;block=end]
		
		[onshow;block=begin;when [object.status]=[seedrando.STATUS_VALIDATED]]
			
			<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.id]&action=modif" class="butAction">[langs.transnoentities(Reopen)]</a></div>
			
		[onshow;block=end]
		
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.id]&action=clone" class="butAction">[langs.transnoentities(ToClone)]</a></div>
		
		<!-- '-+' est l'équivalent d'un signe '<' (TBS oblige) -->
		[onshow;block=begin;when [object.status]-+[seedrando.STATUS_REFUSED]]
			
			<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.id]&action=delete" class="butActionDelete">[langs.transnoentities(Delete)]</a></div>
			
		[onshow;block=end]
		
	[onshow;block=end]
</div>






<table summary="" class="centpercent notopnoleftnoright showlinkedobjectblock" style="margin-bottom: 2px;">
	<tr>
		<td class="nobordernopadding" valign="middle">
			<div class="titre">Mes contact à lier</div>
		</td>
	</tr>
</table>

<form action="xxxxxxxxxxxxxxxx" method="POST">
	<input name="xxxxxxx" type="hidden">
	<input name="action" type="hidden">
	<table class="noborder" width="100%">
		<tbody>
			<tr class="liste_titre">
				<th class="liste_titre">Contacts</th>
				<th class="liste_titre" align="right">
					<select id="groupe" class="flat minwidth200 select2-hidden-accessible" name="groupetest" tabindex="-1" aria-hidden="true">
					
					</select>
					<span class="select2 select2-container select2-container--default" style ="width: 200px; ">
						<span class="selection">
							<span class="select2-selection select2-selection--single flat minwidth200" role="combobox" aria-haspopup="true" tabindex="0" aria-labelledby="select2-group-container">
								<span id="select2-group-container" class="select2-selection__rendered" title="" style ="padding-right: 0px; ">

<!-- 									<select id="listContact" name="listContact" > -->
<!-- 										<option value="facile">Facile</option> -->
<!-- 										<option value="moyenne">Moyenne</option> -->
<!-- 										<option value="difficile">Difficile</option> -->
<!-- 									</select> -->
									[view.showContact;strconv=no]
								</span>
							</span>
						</span>
						<span class="dropdown-wrapper" aria-hidden="true"></span>
					</span>
					<input class="button" value="Ajouter" type="submit">
				</th>
			</tr>
			<tr class="oddeven">
			
				<!--  ici afficher la liste des objets contact en relation avec la rowid de la rando concernée -->
				
				<td class="opacitymedium" colspan="3">
					<div id="test">
						[view.showListContact;strconv=no]
					</div>
					Aucun
				</td>
			</tr>
		</tbody>
	</table>
</form>




<div style="margin-bottom:250px"></div>










[onshow;block=end]