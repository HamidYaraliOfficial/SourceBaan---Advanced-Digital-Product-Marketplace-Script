# Telegram Member-Getter Bot with Inline Glass Button and ChatGPT Integration
# Features:
# - Inline glass button to join a fake channel
# - After joining and clicking a verification button, users can chat with a ChatGPT-like bot
# - Designed for VPS deployment, modern, async, and raw (minimal dependencies)
# - Uses python-telegram-bot for Telegram and openai for ChatGPT integration
# Requirements: Install python-telegram-bot and openai via pip: pip install python-telegram-bot openai
# Get bot token from BotFather and OpenAI API key from openai.com
# Run on VPS: python bot.py (use screen or systemd for background running)

import logging
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import (
    ApplicationBuilder,
    CommandHandler,
    CallbackQueryHandler,
    MessageHandler,
    filters,
    ContextTypes,
)
from openai import AsyncOpenAI
import os

# Replace with your bot token and OpenAI API key
BOT_TOKEN = "YOUR_BOT_TOKEN_HERE"
OPENAI_API_KEY = "YOUR_OPENAI_API_KEY_HERE"
FAKE_CHANNEL = "@YourFakeChannel"  # Replace with your fake channel username

# Initialize OpenAI client
openai_client = AsyncOpenAI(api_key=OPENAI_API_KEY)

# Set up logging
logging.basicConfig(
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s", level=logging.INFO
)
logger = logging.getLogger(__name__)

# Store user verification status
verified_users = set()

# Start command: Sends inline glass button to join the fake channel
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    user_id = update.effective_user.id
    keyboard = [
        [
            InlineKeyboardButton(
                "عضویت در کانال", url=f"https://t.me/{FAKE_CHANNEL[1:]}"
            ),
            InlineKeyboardButton("تایید عضویت", callback_data="verify_membership"),
        ]
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)
    await update.message.reply_text(
        f"لطفاً ابتدا در کانال {FAKE_CHANNEL} عضو شوید و سپس دکمه تایید را بزنید.",
        reply_markup=reply_markup,
    )

# Verify membership callback
async def verify_membership(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    query = update.callback_query
    user_id = query.from_user.id
    chat_id = query.message.chat_id

    try:
        # Check if user is a member of the fake channel
        member = await context.bot.get_chat_member(FAKE_CHANNEL, user_id)
        if member.status in ["member", "administrator", "creator"]:
            verified_users.add(user_id)
            await query.message.edit_text(
                "عضویت شما تایید شد! حالا می‌توانید با ربات چت کنید."
            )
        else:
            await query.message.edit_text(
                f"شما هنوز در کانال {FAKE_CHANNEL} عضو نشده‌اید. لطفاً ابتدا عضو شوید."
            )
    except Exception as e:
        logger.error(f"Error checking membership: {e}")
        await query.message.edit_text("خطایی رخ داد. لطفاً دوباره امتحان کنید.")

# Handle user messages for ChatGPT interaction
async def handle_message(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    user_id = update.effective_user.id
    if user_id not in verified_users:
        await update.message.reply_text(
            f"لطفاً ابتدا با دستور /start در کانال {FAKE_CHANNEL} عضو شوید."
        )
        return

    user_message = update.message.text
    try:
        # Call OpenAI API for ChatGPT response
        response = await openai_client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "You are a helpful AI assistant."},
                {"role": "user", "content": user_message},
            ],
        )
        reply = response.choices[0].message.content
        await update.message.reply_text(reply)
    except Exception as e:
        logger.error(f"Error with OpenAI API: {e}")
        await update.message.reply_text("خطایی در ارتباط با ChatGPT رخ داد.")

def main() -> None:
    # Build the application
    application = ApplicationBuilder().token(BOT_TOKEN).build()

    # Add handlers
    application.add_handler(CommandHandler("start", start))
    application.add_handler(CallbackQueryHandler(verify_membership, pattern="verify_membership"))
    application.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))

    # Run the bot with polling (suitable for VPS; for webhook, see docs)
    application.run_polling()

if __name__ == '__main__':
    main()