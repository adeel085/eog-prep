<?php

$columns = 1;
if (isset($_GET['cols'])) {
	$columns = abs((int)$_GET['cols']);
	$columns = $columns ? $columns : 1;
}

$allowed_paper_sizes = ["letter", "tabloid", "a0", "a1", "a2", "a3", "a4", "a5"];
$paper_size = "letter";
$paper_height = 27.94;
if (isset($_GET['paperSize']) && in_array($_GET['paperSize'], $allowed_paper_sizes)) {
	$paper_size = $_GET['paperSize'];
}

$paper_height_in_cm = get_paper_dimensions_in_cm($paper_size)['height'];

function get_paper_dimensions_in_cm($paper_size)
{

	if ($paper_size == "letter") {
		return ["width" => 21.59, "height" => 27.94];
	} else if ($paper_size == "tabloid") {
		return ["width" => 27.94, "height" => 43.18];
	} else if ($paper_size == "a0") {
		return ["width" => 84.074, "height" => 118.872];
	} else if ($paper_size == "a1") {
		return ["width" => 59.436, "height" => 84.074];
	} else if ($paper_size == "a2") {
		return ["width" => 41.91, "height" => 59.436];
	} else if ($paper_size == "a3") {
		return ["width" => 29.718, "height" => 41.91];
	} else if ($paper_size == "a4") {
		return ["width" => 21.0058, "height" => 29.6926];
	} else if ($paper_size == "a5") {
		return ["width" => 14.7828, "height" => 20.9804];
	}

	return ["width" => 0, "height" => 0];
}

function cm_to_px($centi)
{
	return $centi * 37.79527559055118;
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Questions Sheet</title>
    <script>
        const doc = document.documentElement;
        doc.style.setProperty('--column-width', <?= 100 / $columns ?> + '%');
    </script>
	<style type="text/css">
		body {
			font-size: 20px;
			padding: 0;
			margin: 0;
			width: <?= cm_to_px(get_paper_dimensions_in_cm($paper_size)['width']) ?>px;
		}

		.column img {
			max-width: 100% !important;
		}

		.paper {
			display: flex;
			padding-top: 20px;
			padding-bottom: 20px;
		}

		.paper:not(:last-child) {
			page-break-after: always;
		}

		.column {
			width: calc(var(--column-width) - 40px);
			padding-left: 20px;
			padding-right: 20px;
		}

		.template>p {
			margin-top: 0;
			margin-bottom: 12px;
		}

		.mcq-option-div>p {
			display: inline;
		}

		@page {
			margin: 0;
		}

		#book .paper:first-child {
			padding-top: 80px;
			position: relative;
		}

		#book .worksheet-title {
			font-size: 24px;
			font-weight: bold;
			text-align: center;
			width: 100%;
			position: absolute;
			top: 0;
			left: 0;
			padding-top: 20px;
		}
	</style>
</head>

<body onload="printSheet()">

	<div id="templates" style="display: none;">
		<?php
		foreach ($problems as $index => $problem) {
		?>
			<div class="template" style="margin-bottom: 30px;">
				<b><?= "Question " . ($index + 1) ?>:</b>
				<?= $problem['question_html'] ?>

				<?php
				if ($problem['question_type'] == "mcq") {

					foreach ($problem['answers'] as $choice) {
				?>
						<div class="mcq-option-div">
							<input type="radio"> <?= strip_tags(html_entity_decode($choice['answer'])) ?>
						</div>
				<?php
					}
				}
				?>

				
			</div>
		<?php
		}
		?>
	</div>

	<div id="book">

	</div>

	<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
	<script type="text/javascript">
		function printSheet() {

			let maxColumns = <?= $columns ?>;
			let paperNumber = 1;
			let columnNumber = 1;

			for (let i = 0, loop = 1; i < $("#templates>div").length; i++, loop++) {

				let item = $("#templates>div").get(i);

				if (!$("#book .paper:nth-child(" + paperNumber + ")").length) {

					$("#book").append(`<div class="paper"></div>`);
				}

				if (!$("#book .paper:nth-child(" + paperNumber + ") .column:nth-child(" + columnNumber + ")").length) {

					$("#book .paper:nth-child(" + paperNumber + ")").append(`<div class="column"></div>`);
				}

				// Adding the element to the paper
				$("#book .paper:nth-child(" + paperNumber + ") .column:nth-child(" + columnNumber + ")").append($(item).clone());

				if ($("#book .paper:nth-child(" + paperNumber + ")").outerHeight() >= cmToPx(<?= $paper_height_in_cm ?>)) { // If the paper element is stuck between 2 pages

					$("#book .paper:nth-child(" + paperNumber + ") .column:nth-child(" + columnNumber + ") .template:last-child").remove();
					i--;

					if (columnNumber < maxColumns) {
						columnNumber++;
					} else {
						paperNumber++;
						columnNumber = 1;
					}
				}

				if (loop == 1000) {
					alert("Something went wrong. Please contact your developer!");
					break;
				}
			}

			let div = document.createElement("div");
			div.className = "worksheet-title";
			div.innerHTML = "<?= $worksheetTitle ?>";
			document.querySelector("#book .paper:first-child").prepend(div);

			setTimeout(() => {
                window.print();
            }, 1000);
		}

		// function centimeter to pixels
		function cmToPx(centi) {
			let pixels = centi * 37.79527559055118;
			return pixels;
		}
	</script>
</body>

</html>