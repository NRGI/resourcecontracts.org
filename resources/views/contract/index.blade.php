@extends('layout.app')

@section('content')
    <div class="container">
        <div class="top-container">
            <div class="top-inner-container">
                <div class="search-wrapper">
                    <div class="search-box">
                        <input type="text" placeholder="Search for a document" class="text"/>
                        <input type="submit" class="submit"/>
                    </div>
                    <span class="search-link open">Advanced Search</span>
                    <span class="search-link close">Close Advanced Search</span>
                    <div class="search-input-wrapper">
                        <div class="search-input">
                            <div class="input-wrapper">
                                <label for="">Year (from)</label>
                                <input type="date" />
                            </div>
                            <div class="input-wrapper">
                                <label for="">Year (to)</label>
                                <input type="date" />
                            </div>
                            <div class="input-wrapper">
                                <label for="">Country</label>
                                <select name="" id="">
                                    <option value="">Afghanistan</option>
                                    <option value="">Albania</option>
                                    <option value="">Algeria</option>
                                    <option value="">Andorra</option>
                                    <option value="">Angola</option>
                                </select>
                            </div>
                            <div class="input-wrapper">
                                <label for="">Contract type</label>
                                <select name="" id="">
                                    <option value="">Contract1</option>
                                    <option value="">Contract2</option>
                                    <option value="">Contract3</option>
                                    <option value="">Contract4</option>
                                    <option value="">Contract5</option>
                                </select>
                            </div>
                        </div>
                        <a href="#" class="btn search-btn">Search</a>
                    </div>
                </div>
            </div>
            <div class="breadcrumb-wrapper">
                <div class="breadcrumb">
                    <ul>
                        <li><a href="{{url('/')}}">Home</a></li>
                        <li>All Contracts</li>
                    </ul>
                </div>
                <a href="{{route('contract.create')}}" class="btn add-btn">Add Contract</a>
            </div>
        </div>
        <div class="content">
            <div class="heading">
                <h2>All Contracts</h2>
            </div>
            <div class="content-view list" id="contentView">
                @if($contracts->count() > 0)
                    @foreach($contracts as $contract)
                        <div class="contract mix">
                            <div class="left-contract-wrap">
                                <div class="contract-name"><a href="{{route('contract.show', $contract->id)}}">{{$contract->metadata->project_title}}</a></div>
                                <div class="language"><?php echo $contract->metadata->language;?></div>
                            </div>
                            <div class="right-contract-wrap">
                                <div class="contract-size">{{getFileSize($contract->metadata->file_size)}}</div>
                                <div class="date">{{$contract->created_datetime->format('F d, Y')}}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="contract mix">
                        <td colspan="2">Contract not found.</td>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection