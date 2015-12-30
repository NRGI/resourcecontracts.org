@if($status == $contract_processing_completed)
    <div class="annotation-wrap" id="annotations">
        <h3>@if($annotations->count() > 1) <span class="annotation-count">{{$annotations->count()}}</span> @endif @lang('contract.annotations')<span class="annotation-help">Double click to edit.</span></h3>

        <div class="annotation-list">
            <ul>
                @forelse($annotations as $key => $annotation)
                    <li>
    <p>{{_l("codelist/annotation.annotation_category.{$annotation->category}")}}</p>
                        <span data-pk="{{$annotation->id}}" data-name="text"
                           data-url="{{route('annotation.update')}}" data-type="textarea"
                           class="edit-annotation-text">{!!nl2br($annotation->text)!!}</span>

                        @foreach($annotation->child as $child)

                            <div class="row">
                                <div class="col-md-10">
                                    @if(property_exists($child->annotation, "shapes"))
                                        <span class="annotation-type-icon annotation-pdf-icon"></span>
                                    @else
                                        <span class="annotation-type-icon annotation-text-icon"></span>
                                    @endif
                                    @if(property_exists($child->annotation, "shapes"))
                                           <a href="{{route('contract.annotate',$contract->id)}}#/pdf/page/{{$child->page_no}}/annotation/{{$child->id}}"> Page </a><span data-pk="{{$child->id}}" data-name="page_no"
                                              data-url="{{route('annotation.update')}}"
                                              data-value={{$child->page_no}} data-type="select" class="edit-annotation-page">{{$child->page_no}}</span>
                                    @else
                                            <a href="{{route('contract.annotate',$contract->id)}}#/text/page/{{$child->page_no}}/annotation/{{$child->id}}">  Page </a> <span>{{$child->page_no}}</span>
                                    @endif

                                    @if(!empty($child->article_reference))
                                        <span>-</span>
                                        <span data-pk="{{$child->id}}" data-name="article_reference"
                                              data-url="{{route('annotation.update')}}" data-type="text"
                                              class="edit-annotation-section"> {{$child->article_reference or ''}}</span>
                                    @endif
                                </div>
                                <div class="col-md-2"><a href="javascript:void(0)" data-pk="{{$child->id}}"
                                                         class="annotation-delete-btn">delete</a></div>
                            </div>
                        @endforeach
                    </li>
                @empty
                    <li>
                        @lang('Annotation not created. Please create')
                        <a style="font-size: 14px" href="{{route('contract.annotate', ['id'=>$contract->id])}}">here</a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endif