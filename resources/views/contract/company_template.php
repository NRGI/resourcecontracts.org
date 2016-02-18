<script id="company-template" type="x-tmpl-mustache">
<div id="item{{item}}" class="item">
    <div class="form-group">
        <label for="company_name" class="col-sm-2 control-label">Company Name <span class="red">*</span></label>
        <div class="col-sm-7">
            <input class="required form-control" name="company[{{item}}][name]" type="text" id="company_{{item}}_name">
        </div>
    </div>
    <div class="form-group">
                <label for="participation_share" class="col-sm-2
                control-label">Participation Share</label>
                <div class="col-sm-7">
                    <input class="form-control" step="any" min="0" max="1" name="company[{{item}}][participation_share]" type="text"  id = "company_{{item}}_participation_share">
                </div>
    </div>

    <div class="form-group">
        <label for="jurisdiction_of_incorporation" class="col-sm-2 control-label">Jurisdiction of Incorporation</label>
        <div class="col-sm-7">
           <select class="form-control" name="company[{{item}}][jurisdiction_of_incorporation]" id = "company_{{item}}_jurisdiction">
           <?php foreach ($country_list as $code => $name): ?>
               <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
           <?php endforeach; ?>
           </select>
        </div>
    </div>

    <div class="form-group">
        <label for="registration_agency" class="col-sm-2
        control-label">Registration Agency</label>
        <div class="col-sm-7">
            <input class="form-control" name="company[{{item}}][registration_agency]" id = "company_{{item}}_registration_agency" type="text">
        </div>
    </div>

    <div class="form-group">
        <label for="incorporation_date" class="col-sm-2
        control-label">Incorporation Date</label>
        <div class="col-sm-7">
            <input class="datepicker form-control" placeholder="YYYY-MM-DD" name="company[{{item}}][company_founding_date]"  id = "company_{{item}}_founding_date" type="text">
        </div>
    </div>

    <div class="form-group">
        <label for="company_address" class="col-sm-2
        control-label">Company Address</label>
        <div class="col-sm-7">
            <input class="form-control" name="company[{{item}}][company_address]"  id = "company_{{item}}_address" type="text">
        </div>
    </div>

    <div class="form-group">
        <label for="company_number" class="col-sm-2
        control-label">Company number</label>
        <div class="col-sm-7">
            <input class="form-control" name="company[{{item}}][company_number]" id = "company_{{item}}_number" type="text">
        </div>
    </div>

     <div class="form-group">
        <label for="parent_company" class="col-sm-2 control-label"><?php echo trans('contract.corporate_grouping'); ?></label>
            <div class="col-sm-7">
                <select name="company[{{item}}][parent_company]" class="form-control parent_company" id="corporate_grouping_{{item}}">
                <?php foreach ($groups as $key => $g): ?>
                    <option value="<?php echo $key; ?>"><?php echo $g; ?></option>
                <?php endforeach; ?>
                </select>
            </div>
     </div>

    <div class="form-group">
        <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i> <label for="open_corporate_id" class="col-sm-2
            control-label">OpenCorporates Link</label></a>

        <div class="col-sm-7">
            <input class="digit form-control" name="company[{{item}}][open_corporate_id]" type="text" id = "company_{{item}}_open_corporate_id">
        </div>
    </div>
    <div class="form-group">
      <label for="parent_company" class="col-sm-2
        control-label">Is Operator?</label>
        <div class="col-sm-7">
             <input class="field" name="company[{{item}}][operator]" type="radio" value="1" id="company_{{item}}_operator_yes"> Yes
             <input class="field" name="company[{{item}}][operator]" type="radio" value="0" id="company_{{item}}_operator_no"> No
             <input class="field" name="company[{{item}}][operator]" type="radio" checked="checked" value="-1"> Not Available
        </div>
    </div>
    <div class="delete">delete</div>
</div>


</script>

<script id="concession-template" type="x-tmpl-mustache">
 <div id="concession{{item}}" class="con-item">

        <div class="form-group">
            <label for="license_name" class="col-sm-2 control-label">Concession/License Name</label>
            <div class="col-sm-7">
                <input class="form-control" name="concession[{{item}}][license_name]" type="text" id="license_name_{{item}}">
            </div>
        </div>

        <div class="form-group">
            <label for="license_identifier" class="col-sm-2
            control-label">Concession / License Identifier</label>
            <div class="col-sm-7">
                <input class="form-control" name="concession[{{item}}][license_identifier]" type="text" id="license_identifier_{{item}}">
            </div>

        </div>

    <div class="delete">delete</div>
  </div>


</script>

<script id="government-entity" type="x-tmpl-mustache">

    <div class="government-item">
    <br>
            <div class="form-group">
                <label for="government_entity" class="col-sm-2 control-label">Government Entity</label>
                <div class="col-sm-7">

                    <select class="form-control el_government_entity" name="government_entity[{{item}}][entity]" type="select" id="government_{{item}}_entity">  </select>
                </div>
            </div>

            <div class="form-group">
                <label for="government_identifier" class="col-sm-2
                control-label">Government Identifier</label>
                <div class="col-sm-7">
                    <input class="form-control el_government_identifier" name="government_entity[{{item}}][identifier]" type="text" id="government_{{item}}_identifier" readonly="true">
                </div>
            </div>
        <div class="delete">delete</div>
    </div>

</script>

<script type="text/template" id="document">
    <div class="document">
        <a href="/contract/{{id}}">{{name}}</a>
        <input type="hidden" name="supporting_document[]" value="{{id}}">
        <div class="delete" id="{{id}}">delete</div>
    </div>
</script>