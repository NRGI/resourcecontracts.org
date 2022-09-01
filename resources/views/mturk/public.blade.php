<!DOCTYPE html>
<html>
<head>
	<title>Resource Contracts - MTurk Task</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="{{asset('css/mturk_style.css')}}">
	<script src="{{asset('js/jquery.js')}}"></script>
	<script type="text/javascript">
        $(function () {
            $('#mturk_form').on('submit', function (e) {
                if ($('#feedback').val() == '') {
                    e.preventDefault();
                    alert('Text can\'t be empty.');
                }
            });
        })
	</script>
</head>
<body>
<div class="wrapper">
	<p>In this HIT, you are to transcribe the text <?php echo show_language($langCode);?> as shown in the PDF pages on the right.
	Your HIT will be rejected if we find that there are spelling mistakes or missing text in the transcribed text. 
	Any fraudulent transcriptions will result in you being automatically blocked from the site and you will be reported to Amazon.</p>

    <?php if($assignmentId == 'ASSIGNMENT_ID_NOT_AVAILABLE'):?>
	<p class="disclaimer"><?php echo disclaimer($langCode);?></p>
	<div id="instructions">
        <?php echo other_instructions($langCode);?>
	</div>
    <?php elseif($langCode == 'fr'):?>
	<p class="disclaimer"><?php echo disclaimer('en');?></p>
	<p class="disclaimer"><?php echo disclaimer($langCode);?></p>
	<p><a href="#instructions" class="see_other_instruction">See other instructions</a></p>
    <?php endif;?>

    <?php $external_mturk_url = (env('MTURK_SANDBOX')) ? "https://workersandbox.mturk.com/mturk/externalSubmit" : "https://www.mturk.com/mturk/externalSubmit"; ?>
	<form id="mturk_form" method="post" accept-charset="utf-8" action="<?php echo $external_mturk_url; ?>">
		<input type="hidden" name="workerId" value="<?php echo $workerId;?>" />
		<input type="hidden" name="assignmentId" value="<?php echo $assignmentId;?>" />
		@foreach($contractPdfUrls as $pdf)
		<div class="form-group-wrapper">
			<div class="form-group-item">
				<?php
				$arr = explode('/', rtrim($pdf, '.pdf'));
				$pageNo = end($arr);
				?>
				<textarea name="feedback_{{$pageNo}}" id="feedback_{{$pageNo}}" style="width: 100%" rows="38.5"
					placeholder="Write the text here"></textarea>
			</div>
			<div class="form-group-item">
				<iframe width="100%" height="590" src="{{url('viewer/index.php')}}#<?php echo $pdf;?>"></iframe>
			</div>
		</div>

		@endforeach
		<div>
		<?php if($assignmentId != 'ASSIGNMENT_ID_NOT_AVAILABLE'):?>
				<button type="submit" value="Submit" class="button">Finish and Submit HIT</button>
				<?php else:?>
				<p>Please click on the Accept button for your HIT to be submitted.</p>
		<?php endif;?>
		</div>
		<div>
		<h4 >Once you have accepted the HIT, it needs to be completed and submitted within 5 days</h4>
		<h4 >(Incorrectly transcribed pages will result in the rejection of the entire HIT)</h4>
		</div>
        </form>
</div>

<?php if($assignmentId != 'ASSIGNMENT_ID_NOT_AVAILABLE'):?>
<div id="instructions" class="wrapper">
    <?php echo other_instructions($langCode);?>
</div>
<?php endif;?>
</body>
</html>