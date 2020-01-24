<link rel="stylesheet" type="text/css" href="{{ asset('css/additional.css') }}" >
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/konva.min.js') }}"></script>
<script src="{{ asset('js/pdf.js') }}"></script>
<script src="{{ asset('js/pdf.worker.js') }}"></script>

{{ Form::open(array('url' => 'signPDF', 'method' => 'post', 'enctype' => 'multipart/form-data')) }}

	<button id="upload-button" type="button">Select PDF</button> 
	<input type="file" id="file-to-upload" name="file-to-upload" accept="application/pdf" />

	<div id="pdf-main-container">
		<div id="pdf-loader">Loading document ...</div>
		<div id="pdf-contents">
			<div id="pdf-meta">
				<div id="pdf-buttons">
					<button id="pdf-prev">Previous</button>
					<button id="pdf-next">Next</button>
				</div>
				<div id="page-count-container">Page <div id="pdf-current-page"></div> of <div id="pdf-total-pages"></div></div>
			</div>
			<div id="canvascontent"></div>
			<canvas id="pdf-canvas"></canvas>
			<div id="page-loader">Loading page ...</div>
		</div>
		<div>
			<div>
				<span>
					<input type="text" id="true_page" name="true_page" style="text-align:center;" readonly>
				</span>
			</div>
			<br/>
			<div>
				<span>
					<input type="text" id="posx" name="posx" style="text-align:center;" readonly>
					<input type="text" id="posy" name="posy" style="text-align:center;" readonly>
					<input type="text" id="width" name="width" style="text-align:center;" readonly>
					<input type="text" id="height" name="height" style="text-align:center;" readonly>
				</span>
			</div>
			<br/>
			<div>
				<span>
					<input type="text" id="llx" name="llx" style="text-align:center;" readonly>
					<input type="text" id="lly" name="lly" style="text-align:center;" readonly>
					<input type="text" id="urx" name="urx" style="text-align:center;" readonly>
					<input type="text" id="ury" name="ury" style="text-align:center;" readonly>
				</span>
			</div>
			<br/>
			<div>
				<span>
					<input type="text" id="llx_trans" name="llx_trans" style="text-align:center;" readonly>
					<input type="text" id="lly_trans" name="lly_trans" style="text-align:center;" readonly>
					<input type="text" id="urx_trans" name="urx_trans" style="text-align:center;" readonly>
					<input type="text" id="ury_trans" name="ury_trans" style="text-align:center;" readonly>
				</span>
			</div>
			
		</div>
		<br/>
		<div>
			<div class="form-group">
				{{ Form::label('token', 'Token', array('class' => 'form-control'))}}
                {{ Form::text('token') }}                 
         	</div>
			<div class="form-group">
				{{ Form::label('passphrase', 'Passphrase', array('class' => 'form-control')) }}
                {{ Form::password('passphrase') }}                 
         	</div>
			{{ Form::submit('Submit!') }} 
		</div>
		
	</div>

{{ Form::close() }}

<script>

var __PDF_DOC,
	__CURRENT_PAGE,
	__TOTAL_PAGES,
	__PAGE_RENDERING_IN_PROGRESS = 0,
	__CANVAS = $('#pdf-canvas').get(0),
	__CANVAS_CTX = __CANVAS.getContext('2d');
	__SCALE_VIEW = 2;

var strokeWidth = 1;

function showPDF(pdf_url) {
	$("#pdf-loader").show();

	PDFJS.getDocument({ url: pdf_url }).then(function(pdf_doc) {
		__PDF_DOC = pdf_doc;
		__TOTAL_PAGES = __PDF_DOC.numPages;
		
		// Hide the pdf loader and show pdf container in HTML
		$("#pdf-loader").hide(); 
		$("#pdf-contents").show();
		$("#pdf-total-pages").text(__TOTAL_PAGES);

		// Show the first page
		showPage(1);
	}).catch(function(error) {
		// If error re-show the upload button
		$("#pdf-loader").hide();
		$("#upload-button").show();
		
		alert(error.message);
	});;
}

function showPage(page_no) {
	__PAGE_RENDERING_IN_PROGRESS = 1;
	__CURRENT_PAGE = page_no;
	
	// Disable Prev & Next buttons while page is being loaded
	$("#pdf-next, #pdf-prev").attr('disabled', 'disabled');

	// While page is being rendered hide the canvas and show a loading message
	$("#pdf-canvas").hide();
	$("#page-loader").show();

	$('#true_page').val(page_no);

	// Update current page in HTML
	$("#pdf-current-page").text(page_no);
	
	// Fetch the page
	__PDF_DOC.getPage(page_no).then(function(page) {
		// As the canvas is of a fixed width we need to set the scale of the viewport accordingly
		// var scale_required = 400 / page.getViewport(1).width * __SCALE_VIEW;

		// Get viewport of the page at required scale
		var viewport = page.getViewport(1);

		// Set canvas height
		__CANVAS.height = viewport.height;
		
		// Set canvas width
		__CANVAS.width = viewport.width;

		var renderContext = {
			canvasContext: __CANVAS_CTX,
			viewport: viewport
		};
		
		// Render the page contents in the canvas
		page.render(renderContext).then(function() {
			__PAGE_RENDERING_IN_PROGRESS = 0;

			// Re-enable Prev & Next buttons
			$("#pdf-next, #pdf-prev").removeAttr('disabled');

			var stage = new Konva.Stage({
				container: 'canvascontent',
				width: __CANVAS.width,
				height: __CANVAS.height
			});

			// initial position of signature image	
			document.getElementById("posx").value = 100;
			document.getElementById("posy").value = 50;
			// width and height of signature image, hard coded
			document.getElementById("width").value = 700;
			document.getElementById("height").value = 423;
			
			document.getElementById("llx").value = 100;
			document.getElementById("lly").value = 473;
			document.getElementById("urx").value = 800;  
			document.getElementById("ury").value = 50;  
			
			// Converting from HTML5 Canvas coordinate system to iTextPDF coordinate system
			// HTML5 Canvas defines [0,0] in upper left corner
			// meanwhile iTextPDF defines [0,0] in lower left corner
			document.getElementById("llx_trans").value = 100;
			document.getElementById("lly_trans").value = __CANVAS.height - 100 - 423;
			document.getElementById("urx_trans").value = 800;
			document.getElementById("ury_trans").value = __CANVAS.height - 50;

			var layer = new Konva.Layer();
			layer.id('mylayer');

			var img_test = new Konva.Image({
				x: 100,
				y: 50,
				width: 700,
				height: 423,
				stroke: 'red',
				strokeWidth: strokeWidth,
				draggable: true,
				dragBoundFunc: function(pos){
					var topleft_x = pos.x;
					var topleft_y = pos.y;
					var bottomright_x = pos.x + (this.scaleX() * this.size().width);
					var bottomright_y = pos.y + (this.scaleY() * this.size().height);
					
					// if located in left border then cannot drag in horizontal direction 
					var canDragLeft = topleft_x > strokeWidth;

					// if located in top border then cannot drag in vertical direction
					var canDragTop = topleft_y > strokeWidth;

					// if located in right border then cannot drag in horizontal direction 
					var canDragRight = bottomright_x < __CANVAS.width - strokeWidth;

					// if located in bottom border then cannot drag in vertical direction
					var canDragBottom = bottomright_y < __CANVAS.height - strokeWidth;
					
					var newX = pos.x;
					var newY = pos.y;

					if (!canDragLeft)
					{
						newX = strokeWidth;
					}
					else if (!canDragRight)
					{
						newX = __CANVAS.width - (this.scaleX() * this.size().width) - strokeWidth;
					}

					if (!canDragTop)
					{
						newY = strokeWidth;
					}
					else if (!canDragBottom)
					{
						newY = __CANVAS.height - (this.scaleY() * this.size().height) - strokeWidth;
					}

					document.getElementById("posx").value = newX;
					document.getElementById("posy").value = newY;
					document.getElementById("width").value = this.scaleX() * this.size().width;
					document.getElementById("height").value = this.scaleY() * this.size().height;
					
					document.getElementById("llx").value = newX;
					document.getElementById("lly").value = newY + (this.scaleY() * this.size().height);
					document.getElementById("urx").value = newX + (this.scaleX() * this.size().width);
					document.getElementById("ury").value = newY;
					
					// Converting from HTML5 Canvas coordinate system to iTextPDF coordinate system
					// HTML5 Canvas defines [0,0] in upper left corner
					// meanwhile iTextPDF defines [0,0] in lower left corner
					document.getElementById("llx_trans").value = newX;
					document.getElementById("lly_trans").value = __CANVAS.height - (newY + (this.scaleY() * this.size().height));
					document.getElementById("urx_trans").value = newX + (this.scaleX() * this.size().width);
					document.getElementById("ury_trans").value = __CANVAS.height - newY;

					return {
						x: newX,
						y: newY
					};
				}
			});

			var lock_aspect_ratio = true;
					
			var tr1 = new Konva.Transformer({
				id: 'transformer_box',
				node: img_test,
				keepRatio: lock_aspect_ratio,
				rotateEnabled: false,
				ignoreStroke: true,
				enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right']
			});
			tr1.boundBoxFunc(function(oldBox, newBox){
				if (newBox.x < 0)
				{
					newBox.x = 0;
					newBox.width = oldBox.width;
					if (lock_aspect_ratio){
						newBox.y = oldBox.y;
						newBox.height = oldBox.height;
					}
				}
				else if (newBox.x + newBox.width > __CANVAS.width)
				{
					newBox.width = __CANVAS.width - newBox.x;
					if (lock_aspect_ratio){
						newBox.y = oldBox.y;
						newBox.height = oldBox.height;
					}						
				}

				if (newBox.y < 0)
				{
					newBox.y = 0;
					newBox.height = oldBox.height;
					if (lock_aspect_ratio)
					{
						newBox.x = oldBox.x;
						newBox.width = oldBox.width;
					}						
				}
				else if (newBox.y + newBox.height > __CANVAS.height)
				{
					newBox.height = __CANVAS.height - newBox.y;
					if (lock_aspect_ratio)
					{
						newBox.x = oldBox.x;
						newBox.width = oldBox.width;
					}						
				}
				
				document.getElementById("posx").value = newBox.x;
				document.getElementById("posy").value = newBox.y;
				document.getElementById("width").value = newBox.width;
				document.getElementById("height").value = newBox.height;
				
				document.getElementById("llx").value = newBox.x;
				document.getElementById("lly").value = newBox.y + newBox.height;
				document.getElementById("urx").value = newBox.x + newBox.width;
				document.getElementById("ury").value = newBox.y;
				
				// Converting from HTML5 Canvas coordinate system to iTextPDF coordinate system
				// HTML5 Canvas defines [0,0] in upper left corner
				// meanwhile iTextPDF defines [0,0] in lower left corner
				document.getElementById("llx_trans").value = newBox.x;
				document.getElementById("lly_trans").value = __CANVAS.height - (newBox.y + newBox.height);
				document.getElementById("urx_trans").value = newBox.x + newBox.width;
				document.getElementById("ury_trans").value = __CANVAS.height - newBox.y;

				return newBox;
			});

			// add the layer to the stage
			stage.add(layer);	

			var imageObj_signature = new Image();
			
			imageObj_signature.onload = function() {
				img_test.image(imageObj_signature);

				layer.add(tr1);
				layer.add(img_test);
				layer.draw();
			};
			imageObj_signature.src = 'image/hiclipart.com.png';

			var canvas = document.getElementById('pdf-canvas');
			var dataURL = canvas.toDataURL('image/png');
			
			var imageObj_page = new Image();

			// PDF page data as background	
			imageObj_page.onload = function() {
				// remove previous background
				layer.find('.background').destroy();

				var background = new Konva.Image({
					name: 'background',
					image: imageObj_page,
				});
				// add new one
				layer.add(background);
				background.moveToBottom();
				layer.draw();
			};			
			imageObj_page.src = dataURL;		

			// Show the canvas and hide the page loader
			// $("#pdf-canvas").show();
			$("#page-loader").hide();
		});
	});
}

// Upon click this should should trigger click on the #file-to-upload file input element
// This is better than showing the not-good-looking file input element
$("#upload-button").on('click', function() {
	$("#file-to-upload").trigger('click');
});

// When user chooses a PDF file
$("#file-to-upload").on('change', function() {
	// Validate whether PDF
    if(['application/pdf'].indexOf($("#file-to-upload").get(0).files[0].type) == -1) {
        alert('Error : Not a PDF');
        return;
    }

	//$("#upload-button").hide();

	// Send the object url of the pdf
	showPDF(URL.createObjectURL($("#file-to-upload").get(0).files[0]));
});

// Previous page of the PDF
$("#pdf-prev").on('click', function() {
	if(__CURRENT_PAGE != 1)
		showPage(--__CURRENT_PAGE);
});

// Next page of the PDF
$("#pdf-next").on('click', function() {
	if(__CURRENT_PAGE != __TOTAL_PAGES)
		showPage(++__CURRENT_PAGE);
});

</script>
