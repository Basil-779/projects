from django.shortcuts import render, redirect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from .forms import UserRegisterForm, UserUpdateForm, ProfileUpdateForm
from social_django.models import UserSocialAuth
from django.contrib.auth.forms import AdminPasswordChangeForm, PasswordChangeForm
from django.contrib.sites.shortcuts import get_current_site
from django.utils.encoding import force_bytes, force_text
from django.utils.http import urlsafe_base64_encode, urlsafe_base64_decode
from django.template.loader import render_to_string
from django.core.mail import EmailMessage
from django.contrib.auth import update_session_auth_hash 
from .tokens import account_activation_token
from django.http import HttpResponse
from django.contrib.auth.models import User
from django.utils.translation import gettext as _
from django.contrib.auth.views import LoginView
from django.contrib.auth.forms import AuthenticationForm
from django.utils.translation import gettext_lazy as txt
from django import forms

def forgotpass(request, uidb64, token):
    if request.method == "POST":
        if request.POST['newpass'] != request.POST['repeatpass']:
            mes = _("Passwords do not match!")
            messages.warning(request, mes)
        else:
            try:
                uid = force_text(urlsafe_base64_decode(uidb64))
                user = User.objects.get(id=uid)
            except(TypeError, ValueError, OverflowError, User.DoesNotExist):
                user = None
            if user is not None and account_activation_token.check_token(user, token):
                user.set_password(request.POST['newpass'])
                user.save()
                mes = _("Your password has been changed!")
                messages.success(request, mes)



    return render(request, 'users/newpass.html')

def forgotpwd(request):
    if request.method == "POST":
        e_mail = request.POST['email']
        try:
            user = User.objects.get(email=e_mail)
            current_site = get_current_site(request)
            mail_subject = 'Change your password.'
            message = render_to_string('acc_forgot_email.html', {
                'user': user,
                'domain': current_site.domain,
                'uid':urlsafe_base64_encode(force_bytes(user.id)),
                'token':account_activation_token.make_token(user),
            })
            email = EmailMessage(
                        mail_subject, message, to=[e_mail]
            )
            email.send()
            mes = _("We have sent you an e-mail with the link!")
            messages.success(request, mes)
        except User.DoesNotExist:
            mes = _("No such user!")
            messages.warning(request, mes)
        
    return render(request, 'users/forgotpwd.html')
        


def register(request):
    if request.method == 'POST':
        form = UserRegisterForm(request.POST)
        if form.is_valid():
            user = form.save(commit=False)
            formmail = user.email
            try:
                userwithmail = User.objects.get(email=formmail)
            except User.DoesNotExist:
                user.is_active = False
                user.save()
                current_site = get_current_site(request)
                mail_subject = 'Activate your blog account.'
                message = render_to_string('acc_active_email.html', {
                    'user': user,
                    'domain': current_site.domain,
                    'uid':urlsafe_base64_encode(force_bytes(user.id)),
                    'token':account_activation_token.make_token(user),
                })
                to_email = form.cleaned_data.get('email')
                email = EmailMessage(
                            mail_subject, message, to=[to_email]
                )
                email.send()
                mes = _("We have sent you an activation link!")
                messages.success(request, mes)
                return redirect('login')
            
            mes = _("Sorry, this email is already in use")
            messages.success(request, mes)

    else:
        form = UserRegisterForm()
    return render(request, 'users/register.html', {'form': form})

def check_social(user):
    try:
        github_login = user.social_auth.get(provider='github')
    except UserSocialAuth.DoesNotExist:
        github_login = None

    try:
        intra42_login = user.social_auth.get(provider='intra42')
    except UserSocialAuth.DoesNotExist:
        intra42_login = None

    try:
        yandex_login = user.social_auth.get(provider='yandex-oauth2')
    except UserSocialAuth.DoesNotExist:
        yandex_login = None
    can_disconnect = (user.social_auth.count() > 1 or user.has_usable_password())

    return {
        'github_login': github_login,
        'intra42_login': intra42_login,
        'yandex_login': yandex_login,
        'can_disconnect': can_disconnect
    }

@login_required
def profile(request):
    soc_info = check_social(request.user)
    if request.method == 'POST':
        u_form = UserUpdateForm(request.POST, instance=request.user)
        p_form = ProfileUpdateForm(request.POST,
                                   request.FILES,
                                   instance=request.user.profile)
        if u_form.is_valid() and p_form.is_valid():
            u_form.is_active = False
            u_form.save()
            p_form.save()            
            mes = _("Updated!")
            messages.success(request, mes)

    else:
        u_form = UserUpdateForm(instance=request.user)
        p_form = ProfileUpdateForm(instance=request.user.profile)

    context = {
        'u_form': u_form,
        'p_form': p_form,
        'soc_info': soc_info
    }

    return render(request, 'users/profile.html', context)


def activate(request, uidb64, token):
    try:
        uid = force_text(urlsafe_base64_decode(uidb64))
        user = User.objects.get(id=uid)
    except(TypeError, ValueError, OverflowError, User.DoesNotExist):
        user = None
    if user is not None and account_activation_token.check_token(user, token):
        user.is_active = True
        user.save()
        mes = _("Thank you for your e-mail confirmation. Now you can log in")
        messages.success(request, mes)
        context = {
            'is_valid': 1
        }
    else:
        mes = _("Activation link is invalid!")
        messages.warning(request, mes)
        context = {
            'is_valid': 0
        }
    return render(request, 'users/activate.html', context)

def show_404(request, exception):
    return render(request, 'users/404.html')

def show_500(request):
    return render(request, 'users/500.html')

@login_required
def password(request):
    if request.user.has_usable_password():
        PasswordForm = PasswordChangeForm
    else:
        PasswordForm = AdminPasswordChangeForm

    if request.method == 'POST':
        form = PasswordForm(request.user, request.POST)
        if form.is_valid():
            form.save()
            update_session_auth_hash(request, form.user)
            messages.success(request, _('Your password was successfully updated!'))
            return redirect('password')
        else:
            messages.error(request, 'Please correct the error below.')
    else:
        form = PasswordForm(request.user)
    return render(request, 'users/password.html', {'form': form})

class MyAuthForm(AuthenticationForm):

    def get_invalid_login_error(self):
        try:
            user = User.objects.get(username=self.cleaned_data.get('username'))
        except User.DoesNotExist:
            return forms.ValidationError(
                self.error_messages['invalid_login'],
                code='invalid_login',
                params={'username': self.username_field.verbose_name},
            )
        if not user.is_active and user:
            raise forms.ValidationError(
                self.error_messages['inactive'],
                code='inactive',)
        else:
            return forms.ValidationError(
                self.error_messages['invalid_login'],
                code='invalid_login',
                params={'username': self.username_field.verbose_name},
            )


class MyLoginView(LoginView):
    authentication_form = MyAuthForm
