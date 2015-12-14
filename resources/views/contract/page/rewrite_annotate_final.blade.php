@extends('layout.app-full')

@section('css')
    <style>
        .box-pop {
            position: absolute;
            border: 1px solid #ccc;
            background: #fff;
            height: 70px;
            padding: 20px;
            width: 200px;
        }
        #text {
            background: #e5e5e5;
            padding: 5px;
            height: 600px;
            overflow: auto;
        }
        .note {background-color: #fff16e}
        .page {background: #fff; margin: 20px; padding: 10px; height: 500px;}
    </style>
@stop
@section('script')

    <script src="{{asset('annotation/jquery-migrate-1.2.1.js')}}"></script>
    <script src="{{asset('annotation/jquery-ui.min.js')}}"></script>
    <script src="{{asset('annotation/pdf.js')}}"></script>
    <script src="{{asset('annotation/pdf.init.js')}}"></script>
    <script src="{{asset('annotation/jbox.js')}}"></script>
    <script src="{{asset('annotation/rangy-core.js')}}"></script>
    <script src="{{asset('annotation/rangy-highlighter.js')}}"></script>
    <script src="{{asset('annotation/rangy-classapplier.js')}}"></script>
    <script src="{{asset('annotation/rangy.init.js')}}"></script>
    <script>
        $(function(){
            $('.btn-annotation-create').click(function(){
                $('.annotation-form').slideToggle();
            });

            @foreach($contract->annotations as $annotation)
            <?php $pages = json_decode($annotation->annotation);?>
            @if(!is_null($pages))
            @foreach($pages as $page)
            @if($page->type == 'text')
            restoreSelection(document.getElementById('summary'), JSON.parse('{!!$page->position!!}'));
            highlighter.highlightSelection("note", {containerElementId: "summary"});
            @endif

            rangy.getSelection().removeAllRanges();

            @endforeach
        @endif
            @endforeach

            })



    </script>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div style="overflow: hidden">
                    <h2 class="pull-left">Annotations</h2>
                    <button style="margin-top: 20px;" class="pull-right btn btn-primary btn-annotation-create">Create</button>
                </div>
                    <div class="annotation-form" style="display: none;">
                        {!! Form::open(['route'=>['contract.annotate.new',$contract->id],'method' => 'post']) !!}
                        <div class="form-group">
                            {!! Form::select('category',[''=>'Select', 'np' => 'Nepal'] , null , ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::textarea('text', '', ['placeholder'=>'Annotation...', 'class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
                        </div>
                        <ul class="annotated-text-list">

                        </ul>
                        {!! Form::close() !!}
                    </div>
                <ul style="padding: 0px;">
                @foreach($contract->annotations as $annotation)
                    <li style="background: #cdcdcd; padding: 5px; list-style: none; margin-bottom: 10px;">
                        <h4>{{$annotation->text}}</h4>
                        <p>{{$annotation->category}}</p>
                        <?php $pages = json_decode($annotation->annotation);?>

                        @if(!is_null($pages))
                            @foreach($pages as $page)
                                <div style="background: #fff; padding: 5px; margin-bottom: 10px;">
                                @if($page->type == 'text')
                                   <p>{{$page->text}}</p>
                                    <p>Page#{{$page->page}}</p>
                                @endif
                                @if($page->type == 'pdf')
                                    <p>PDF</p>
                                    <p>Page#{{$page->page}}</p>
                                @endif
                                </div>
                            @endforeach
                        @endif
                    </li>
                @endforeach
                </ul>
            </div>

            <div class="col-md-9">        <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#text" aria-controls="text" role="tab" data-toggle="tab">Text</a></li>
                    <li role="presentation"><a href="#pdf" aria-controls="pdf" class="pdfTab" role="tab" data-toggle="tab">PDF</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="text">
                        <div id="summary">
                                <p class="page" data-page="1">1. Vivamus semper dignissim lacinia. Mauris placerat ante nibh, eget fermentum lorem suscipit a. Curabitur pretium venenatis justo, et viverra felis faucibus ac. Aliquam at sem augue. Aenean tincidunt mi libero, vel gravida dolor finibus eu. In porttitor leo sed elementum venenatis. Aliquam laoreet sapien consequat elementum ullamcorper. Mauris venenatis aliquam ultricies.</p>
                                <p class="page" data-page="2">2. Cras elementum, augue sit amet lobortis dignissim, purus diam feugiat risus, vel condimentum leo nisl at nibh. Nam luctus a est et tempus. Phasellus aliquam nisl urna, sed aliquet metus cursus nec. Sed nibh mauris, maximus ac pellentesque id, venenatis a diam. Morbi facilisis nibh non urna volutpat semper. Ut egestas fringilla nulla et facilisis. Mauris sit amet libero vulputate, interdum arcu eu, mattis purus. Quisque nec nulla et augue sodales porta eget in leo. Sed lobortis arcu a massa varius, sed accumsan est ullamcorper. Sed nibh arcu, iaculis vel pretium ac, imperdiet eu lacus. Donec commodo mollis tristique. Praesent et eros facilisis, eleifend dolor sed, eleifend libero. Nullam quis efficitur quam, a tempor eros. Donec id fermentum dui. Ut vulputate lacus ut mauris placerat pharetra nec sed lacus. Proin porttitor orci id dui ultrices posuere fringilla in urna.</p>
                                <p class="page" data-page="3">3. Maecenas nibh leo, laoreet ut erat ornare, sagittis porta eros. Cras efficitur mattis lectus, quis laoreet velit suscipit ut. In finibus magna at felis tristique bibendum. Suspendisse hendrerit tincidunt lacus, at ullamcorper diam tempor vel. Donec et cursus neque. Sed id tristique neque. Vestibulum egestas volutpat justo, non sollicitudin velit vehicula a. Nulla porta pharetra nunc, vitae interdum nulla commodo eu. In ac massa purus. Nulla suscipit nisl arcu, nec ullamcorper lorem dignissim ut. Fusce at faucibus libero, eget vulputate erat. Nunc consequat vulputate semper. Fusce ut felis porta, iaculis felis a, congue magna.</p>
                                <p class="page" data-page="4">4. Quisque imperdiet nunc id erat dictum efficitur. Sed blandit diam est, sit amet pharetra justo placerat ac. Cras tristique mauris eu nulla mattis, ut interdum odio commodo. Quisque pulvinar mattis augue et aliquet. Fusce gravida dui a risus euismod pharetra eu eget tortor. Suspendisse facilisis magna posuere mi rhoncus, ut posuere lectus convallis. In orci erat, tincidunt quis justo vitae, accumsan rutrum ante.</p>
                                <p class="page" data-page="5">5. Vivamus sed posuere quam, in fringilla lacus. Duis id suscipit mi. Nullam consequat sodales nisl vel cursus. Donec tellus diam, ullamcorper vitae elementum id, malesuada sed metus. Cras ut est imperdiet, bibendum orci vitae, ornare arcu. In ut sodales eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras sed nulla et lorem luctus auctor. Sed magna dolor, porta sed neque sed, semper vehicula justo. Mauris vel cursus lacus. Suspendisse consectetur auctor velit, at interdum nisi feugiat a. Integer consectetur sit amet arcu sed consequat. Nunc at pulvinar odio, in bibendum elit. Aenean eget ligula iaculis, consectetur nisl at, dapibus nisi.</p>
                                <p class="page" data-page="6">6. Nunc bibendum efficitur neque. Vestibulum elit libero, elementum id consequat quis, dictum non risus. Phasellus non dolor congue nisi laoreet semper a quis tellus. Fusce suscipit leo vitae massa finibus auctor. Phasellus tempor metus at mi scelerisque faucibus. Aenean et purus cursus, porta justo ac, vulputate lorem. Morbi sodales enim nibh, ut gravida purus faucibus id. Curabitur vitae diam eu ipsum lacinia finibus vel nec ipsum. Etiam at tellus risus.</p>
                            </div>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="pdf">
                        <div>
                            <button id="prev">Previous</button>
                            <button id="next">Next</button>
                            <button class="zoom" data-scale="0.5">Zoom 50%</button>
                            <button class="zoom" data-scale="1.0">Zoom 100%</button>
                            <button class="zoom" data-scale="1.5">Zoom 150%</button>
                            <button class="zoom" data-scale="2.0">Zoom 200%</button>
                            &nbsp; &nbsp;
                            <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
                        </div>

                        <div style="height: 600px; overflow: auto">
                            <div id="canvas" style="border:1px solid red; position: relative; height: 900px; width: 600px;">
                                <canvas id="the-canvas"></canvas>
                                @foreach($contract->annotations as $annotation)
                                    <?php $pages = json_decode($annotation->annotation);?>
                                @if(!is_null($pages))
                                    @foreach($pages as $page)
                                        @if($page->type == 'pdf')
                                        <?php $position = json_decode($page->position); ?>
                                            <div style="position: absolute;width: {{$position->width}}px; height: {{$position->height}}px; top:{{$position->top}}px;left:{{$position->left}}px; border:1px solid #000">
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                    @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop