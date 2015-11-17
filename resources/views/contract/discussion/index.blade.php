<style>
    div.error {
        text-align: left;
        color: #AC0202;
        font-size: 13px;
    }

    .comment-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .comment-list .panel-default {
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .comment-list .panel-default p {
        font-size: 12px;
    }

    .comment-list .panel-default .label {
        font-size: 11px;
        color: #fff !important;
    }

    .comment-list .panel-default p.comment-user {
        font-size: 13px;
        font-weight: bold;
    }

    .comment-list .panel-default .panel-heading {
        padding: 10px 10px 0px;
    }

    .comment-key {margin-bottom: 10px;}
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">{{$contract->title}}</h4>
</div>
<div class="modal-body">

    @if($type == 'metadata')
        <div class="comment-key">
        <?php  $key_text = explode('-', $key);?>
        @if(count($key_text) > 1)
            <?php
            $i = $key_text[1];
            $key_text = $key_text[0];
            ?>
            @if(in_array($key_text, ['entity','identifier']))
                <p>
                    <strong>@lang('contract.government_'.$key_text):</strong>
                    {{$contract->metadata->government_entity[$i]->$key_text}}
                </p>
            @elseif(in_array($key_text, ['license_name','license_identifier']))
                <strong>@lang('contract.'.$key_text):</strong>
                {{$contract->metadata->concession[$i]->$key_text}}
            @elseif($key_text == 'operator')
                <strong>@lang('contract.'.$key_text):</strong>
                <?php $operator = $contract->metadata->company[$i]->$key_text;?>
                @if($operator==1)Yes @elseif($operator==0) No @elseif($operator==-1) Not Available @endif
            @else
                <strong>@lang('contract.'.$key_text):</strong>
                {{$contract->metadata->company[$i]->$key_text}}
            @endif

        @else
            <?php $key_text = $key_text[0];?>
            <p>
                <strong>@lang('contract.'.$key_text):</strong>
                @if($key_text == 'resource')
                    {{join(', ',$contract->metadata->$key_text)}}
                @elseif($key_text == 'country')
                    {{$contract->metadata->$key_text->name}}
                @elseif($key_text == 'category')
                    <?php $category = $contract->metadata->$key_text;?>
                    {{config('metadata.category.'.$category[0])}}
                @else
                    {{$contract->metadata->$key_text}}
                @endif
            </p>
        @endif
        </div>
    @endif


    <div class="comment-list">
        @forelse($discussions as $discussion)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <p class="comment-user"><i class="fa fa-user"></i> {{$discussion->user->name}}</p>
                    @if($discussion->status == 1) <span class="label label-success pull-right">Resolved</span> @endif
                    <p class="comment-time"><i class="fa fa-clock-o"></i> {{$discussion->created_at}}</p>
                </div>
                <div class="panel-body"> {!!nl2br($discussion->message)!!}</div>
            </div>
        @empty
            <div class="panel panel-default">
                <div class="panel-body">Comment not added yet.</div>
            </div>
        @endforelse
    </div>
</div>
<div class="modal-footer">
    {!! Form::open(['url' => route('contract.discussion.create', ['id'=>$contract->id, 'type'=>$type, 'key'=>$key]), 'method' => 'post', 'id'=>'commentForm']) !!}
    <textarea placeholder="Write comment..." id="commentField" name="comment" style="width: 100%; height: 70px;"></textarea>
    <label>
        {!! Form::checkbox('status', '1', (isset($discussions[0]) && $discussions[0]->status == '1'), ['id' => 'name']) !!}
        Mark as resolved
    </label>
    <br/>
    <button type="submit" class="btn btn-primary">Save</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    {!! Form::close() !!}
</div>

<script>
    $(function () {
        $('#commentForm').on('submit', function (e) {
            e.preventDefault();
            var $this = $(this);
            $this.find('div.error').remove();

            if ($this.find('#commentField').val() == '') {
                $('#commentField').after('<div class="error"> Comment is required.</div>');
                return false;
            }

            var action = $this.prop('action');
            var array = action.split('/');
            var key = '.key-' + array[array.length - 1];

            $this.find('.btn-primary').attr('disabled', 'disabled')
            $.ajax({
                url: action,
                type: $this.prop('method'),
                data: $this.serialize(),
                dataType: "JSON",
                success: function (response) {
                    if (response.result == true) {
                        var html = '';
                        $.each(response.message, function (index, dis) {
                            var status = dis.status == '1' ? ' <span class="label label-success pull-right">Resolved</span>' : '';
                            html += '<div class="panel panel-default">' +
                            '<div class="panel-heading">' +
                            '<p class="comment-user"><i class="fa fa-user"></i> ' + dis.user.name + '</p>' +
                            status +
                            '<p class="comment-time"><i class="fa fa-clock-o"></i> ' + dis.created_at + '</p>' +
                            '</div>' +
                            '<div class="panel-body">' + nl2br(dis.message) + '</div>' +
                            '</div>';
                        });
                        $('.comment-list').html(html);
                        $this.find('#commentField').val('');
                        var key_html = '';
                        if (response.message[0].status == 1) {
                            key_html = '<span class="label label-success">(' + response.message.length + ') Resolved</span>';
                        } else {
                            key_html = '<span  class="label label-red">(' + response.message.length + ') Open</span>';
                        }
                        $(key).html(key_html);

                    } else {
                        $this.find('#commentField').after('<div class="error">' + response.message + '</div>')
                    }
                },
                error: function (e) {
                    alert('Connection error');
                },
                complete: function () {
                    $this.find('.btn-primary').removeAttr('disabled');
                }
            })

            function nl2br(str, is_xhtml) {
                var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
                return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
            }

        });
    })
</script>
