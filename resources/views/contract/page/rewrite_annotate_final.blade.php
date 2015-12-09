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
    </style>
@stop
@section('script')
    <script src="{{asset('annotation/jquery-migrate-1.2.1.js')}}"></script>
    <script src="{{asset('annotation/jquery-ui.min.js')}}"></script>
    <script src="{{asset('annotation/pdf.js')}}"></script>
    <script src="{{asset('annotation/jbox.js')}}"></script>
    <script src="{{asset('annotation/pdf.init.js')}}"></script>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                sdfsdf
            </div>

            <div class="col-md-9">        <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#text" aria-controls="text" role="tab" data-toggle="tab">Text</a></li>
                    <li role="presentation"><a href="#pdf" aria-controls="pdf" class="pdfTab" role="tab" data-toggle="tab">PDF</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="text">
                        Text
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
                            <div id="canvas" style="border:1px solid red;">
                                <canvas id="the-canvas" style=""></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop