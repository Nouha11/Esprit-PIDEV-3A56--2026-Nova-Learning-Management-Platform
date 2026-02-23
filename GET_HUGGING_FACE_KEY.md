# How to Get Your Hugging Face API Key

## Quick Steps

1. **Visit Hugging Face**
   - Go to: https://huggingface.co/

2. **Sign Up / Sign In**
   - Click "Sign Up" (top right) if you don't have an account
   - Or "Sign In" if you already have one
   - It's completely FREE - no credit card required!

3. **Go to Settings**
   - Click your profile picture (top right)
   - Select "Settings" from dropdown

4. **Access Tokens**
   - In the left sidebar, click "Access Tokens"
   - Or go directly to: https://huggingface.co/settings/tokens

5. **Create New Token**
   - Click "New token" button
   - Give it a name (e.g., "Quiz Hints")
   - Select "Read" permission (that's all you need)
   - Click "Generate token"

6. **Copy Your Token**
   - Copy the token that starts with `hf_...`
   - It will look like: `hf_aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890`

7. **Add to .env File**
   - Open your `.env` file
   - Find the line: `HUGGING_FACE_API_KEY=your_hugging_face_api_key_here`
   - Replace `your_hugging_face_api_key_here` with your actual token
   - Save the file

8. **Clear Cache**
   ```bash
   php bin/console cache:clear
   ```

9. **Test It**
   ```bash
   php bin/console app:test-quiz-hint --question-id=1
   ```

## Example

Your `.env` file should look like this:

```env
###> Hugging Face API ###
HUGGING_FACE_API_KEY=hf_aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890
###< Hugging Face API ###
```

## Important Notes

- ✅ **Completely FREE** - No credit card required
- ✅ **No Expiration** - Token doesn't expire unless you delete it
- ✅ **Read Permission** - Only needs "Read" access, not "Write"
- ⚠️ **Keep Secret** - Don't share your token publicly
- ⚠️ **Don't Commit** - Never commit `.env` to git (it's in `.gitignore`)

## Troubleshooting

### "Environment variable not found"
- Make sure you saved the `.env` file
- Run `php bin/console cache:clear`
- Restart your development server if running

### "Invalid token"
- Check you copied the entire token (starts with `hf_`)
- Make sure there are no extra spaces
- Verify token is active in Hugging Face settings

### "Model loading timeout"
- First request takes 5-10 seconds (model wakes up)
- Subsequent requests are faster (3-5 seconds)
- This is normal behavior

## What You Get

With Hugging Face API, you get:
- 🎯 **Free AI hints** for quiz questions
- 🚀 **Mistral-7B model** (7 billion parameters)
- 💡 **Smart hints** that guide without revealing answers
- 🔄 **Unlimited requests** (generous free tier)
- 🔒 **Privacy** - Can self-host if needed

## Need Help?

If you have issues:
1. Check the token is correct in `.env`
2. Clear cache: `php bin/console cache:clear`
3. Check Hugging Face status: https://status.huggingface.co/
4. Review logs: `var/log/dev.log`

---

**Ready to test?** Once you've added your token, run:
```bash
php bin/console app:test-quiz-hint --question-id=1
```

The first request may take 5-10 seconds as the model loads, but subsequent requests will be faster!
