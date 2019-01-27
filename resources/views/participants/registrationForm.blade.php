@extends('layouts.participant.master')

@section('content')
    <div class="row" style="text-align: center">
        <h4 style="margin-bottom: 2%">Registration Form:</h4>
        <form action="{{route('participant.registration')}}" method="POST">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-2">
                    <label for="document">ID/Passport:*</label>
                    <div class="form-group">
                        <input type="text" name="document" id="document" value="{{old('document')}}" autofocus>
                        @if ($errors->has('document'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('document') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <label for="phone">Phone number:*</label>
                    <div class="form-group">
                        <input type="text" name="phone" id="phone" value="{{old('phone')}}">
                        @if ($errors->has('phone'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('phone') }}</strong>
                                    </span>
                        @endif
                    </div>



                    <label for="tshirt">T-Shirt size:*</label>
                    <div class="form-group">
                        <select name="tshirt" id="tshirt">
                            <option disabled selected>-</option>
                            <option value="xs" @if (old('tshirt') == 'xs') selected="selected" @endif>XS</option>
                            <option value="s" @if (old('tshirt') == 's') selected="selected" @endif>S</option>
                            <option value="m" @if (old('tshirt') == 'm') selected="selected" @endif>M</option>
                            <option value="l" @if (old('tshirt') == 'l') selected="selected" @endif>L</option>
                            <option value="xl" @if (old('tshirt') == 'xl') selected="selected" @endif>XL</option>
                            <option value="xxl" @if (old('tshirt') == 'xxl') selected="selected" @endif>XXL</option>
                        </select>
                        @if ($errors->has('tshirt'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('tshirt') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <label for="esncard">ESNcard no:</label>
                    <div class="form-group">
                        <input type="text" name="esncard" id="esncard" value="{{old('esncard')}}">
                        @if ($errors->has('esncard'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('esncard') }}</strong>
                                    </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="facebook">Facebook URL:</label>
                    <div class="form-group">
                        <input type="text" name="facebook" id="facebook" value="{{old('facebook')}}">
                        @if ($errors->has('facebook'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('facebook') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <label for="allergies">Allergies:</label>
                    <div class="form-group">
                        <textarea rows="3" type="text" name="allergies" id="allergies" placeholder="None">{{old('allergies')}}</textarea>
                        @if ($errors->has('allergies'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('allergies') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <label for="meal">Food restrictions:</label>
                    <div class="form-group">
                        <textarea rows="3" type="text" name="meal" id="meal" placeholder="None">{{old('meal')}}</textarea>
                        @if ($errors->has('meal'))
                            <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('meal') }}</strong>
                                    </span>
                        @endif
                    </div>

                    @csrf
                </div>
            </div>
            <input type="submit" class="btn btn-success">

        </form>
    </div>
@endsection