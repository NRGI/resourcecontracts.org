@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">All Contracts <a href="{{route('contract.create')}}" class="pull-right btn btn-primary">Add Contract</a></div>


        <div class="panel-body">

            <table class="table table-responsive">
                @forelse($contracts as $contract)
                    <tr>
                        <td width="70%">
                            <i class="glyphicon glyphicon-file"></i>
                            <a href="{{route('contract.show', $contract->id)}}">{{$contract->metadata->project_title}}</a>
                            <span class="label label-default"><?php echo $contract->metadata->language;?></span>
                        </td>
                        <td align="right">{{getFileSize($contract->metadata->file_size)}}</td>
                        <td align="right">{{$contract->created_datetime->format('F d, Y')}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">Contract not found.</td>
                    </tr>
                @endforelse

            </table>


        </div>
    </div>
@endsection