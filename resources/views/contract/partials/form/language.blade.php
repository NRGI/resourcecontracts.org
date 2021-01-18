<div class="block translation-lang-wrapper" id="language">
	<div class="row clearfix">
		<div class="col-md-8 lang-choose-section">
			<p>Contract information translations:</p>
			<span>
				@foreach($lang->translation_lang() as $l)
					<?php
					$hash = ($view == 'show') ? '#language' : null;

					if ($lang->defaultLang() == $l['code']):
						$route      = route(
						        sprintf('contract.%s', $view), ['id' => $contract->id]);
						$edit_route = route(
								sprintf('contract.%s.trans', 'edit'),
								['id' => $contract->id, 'lang' => null]
						);
					else:
						$route      = route(
								sprintf('contract.%s.trans', $view),
								['id' => $contract->id, 'lang' => $l['code']]
						);
						$edit_route = route(
								sprintf('contract.%s.trans', 'edit'),
								['id' => $contract->id, 'lang' => $l['code']]
						);
					endif;
					?>
					@if($lang->current_translation() == $l['code'] && (!isset($page) || $page != 'index'))
						<?php $edit_pate = $edit_route;?>
						<a class="lang-link-active">{{$l['name']}}</a>
					@else
						@if($contract->hasTranslation($l['code']))
							<a class="lang-link" href="{{$route}}{{$hash}}">{{$l['name']}}</a>
						@else
							<a href="#"
							   class="lang-link-disabled"
							   data-placement="bottom"
							   data-toggle="popover"
							   data-trigger="focus"
							   data-content="This contract hasn’t been translated into {{$l['name']}} yet.
							   <center><a class='add-lang-btn' href='{{$edit_route}}'>+ Add {{$l['name']}}
									   translation</a></center>">
								{{$l['name']}}
							</a>
						@endif
					@endif
				@endforeach
			</span>
		</div>
		@if($view == 'show' && (!isset($page) || $page != 'index' ))
			<div class="col-md-4">
				<a href="{{$edit_pate}}" class="pull-right edit-section">
					<i class="glyphicon glyphicon-edit"></i>
					Edit contract information
				</a>
			</div>
		@endif
	</div>
</div>
