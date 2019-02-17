@extends('layouts.participant.master')

@section('content')
    <div class="row" style="text-align: center">
        <div class="row hidden-xl hidden-lg hidden-md hidden-sm">
            <img src="{{asset('images/event_info.jpg')}}" alt="Event Information"
                 style="max-width:90%;margin-bottom:3%">
        </div>
        <div class="row hidden-xs">
            <img src="{{asset('images/event_info.jpg')}}" alt="Event Information"
                 style="max-width:40%;margin-bottom:3%">
        </div>
        <h3 style="margin-bottom: 2%">Registration Form:</h3>
        <div style="text-align: left">
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

                        <label for="facebook">Facebook URL:*</label>
                        <div class="form-group">
                            <input type="text" name="facebook" id="facebook" value="{{old('facebook')}}">
                            @if ($errors->has('facebook'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('facebook') }}</strong>
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
                        <label for="allergies">Allergies:</label>
                        <div class="form-group">
                            <textarea rows="3" name="allergies" id="allergies"
                                      placeholder="None">{{old('allergies')}}</textarea>
                            @if ($errors->has('allergies'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('allergies') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <label for="meal">Food restrictions:</label>
                        <div class="form-group">
                            <textarea rows="3" name="meal" id="meal"
                                      placeholder="None">{{old('meal')}}</textarea>
                            @if ($errors->has('meal'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('meal') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <label for="comments">Comments:</label>
                        <div class="form-group">
                            <textarea rows="3" name="comments" id="comments">{{old('comments')}}</textarea>
                            @if ($errors->has('comments'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('comments') }}</strong>
                                    </span>
                            @endif
                        </div>

                        @csrf
                    </div>
                </div>
                <div class="row" style="margin-left: 3%;margin-right: 3%;text-align: left;">
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <div class="form-group" style="text-align: left">
                            <input type="checkbox" id="consent" name="consent"> I accept the event's <a target="_blank"
                                                                                                        href="{{route('event.terms')}}">terms
                                & conditions</a>.

                            @if ($errors->has('consent'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('consent') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="form-group" style="text-align: left">
                            <input type="checkbox" id="gdpr" name="gdpr"> I acknowledge that my application data will be
                            shared with the Organising Committee in order to facilitate various
                            logistics matters such as food, accommodation and social programme. I also understand that
                            this means my application data may be shared with third parties providing services to
                            the event such as accommodation, catering and venue (only the minimum required application
                            data will be shared with these third parties). *


                            @if ($errors->has('gdpr'))
                                <span class="help-block">
                                        <strong style="color: red;">{{ $errors->first('gdpr') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <p style="text-align: left">By clicking submit, having been informed of the <a target="_blank"
                                                                                                       href="https://esngreece.gr/privacy-policy">Privacy
                                Policy</a> of Federation of Erasmus Student Network - Greece (hereafter "ESN Greece")
                            who is the Data Controller, I grant ESN
                            Greece the right to process the Data I provided it with the present, in accordance with the
                            GDPR, for the purpose of communication (receiving updates & AGM-related information
                            via email or other media). I understand that the consent to the processing of my Data may be
                            revoked by sending an email at <a href="mailto:dpo@esngreece.gr">dpo@esngreece.gr</a></p>
                    </div>
                </div>
                <div class="row" style="text-align: center">
                    <input style="text-align: center" type="submit" class="btn btn-success" value="Next step">
                </div>
            </form>
        </div>
    </div>
@endsection