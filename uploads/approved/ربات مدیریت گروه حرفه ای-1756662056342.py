# Telegram Group Management Bot
# Built with python-telegram-bot library (modern, async-friendly)
# Features:
# - Access restricted to admins and owner only
# - /ban: Bans a user when replied to their message
# - /slot: Sends a random slot machine animation (using Telegram's dice feature)
# - /meo: Replies with a long "meow" sound
# - /tas: Sends a random dice roll (using Telegram's dice feature)
# Designed for VPS deployment: Use polling for simplicity, or set up webhook for production
# Requirements: Install python-telegram-bot via pip: pip install python-telegram-bot
# Get your bot token from BotFather on Telegram
# Run on VPS: python bot.py (use screen or systemd for background running)

import logging
from telegram import Update, Dice
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes

# Replace with your bot token
BOT_TOKEN = 'YOUR_BOT_TOKEN_HERE'

# Set up logging
logging.basicConfig(format='%(asctime)s - %(name)s - %(levelname)s - %(message)s', level=logging.INFO)
logger = logging.getLogger(__name__)

# Helper function to check if user is admin or owner
async def is_admin(update: Update, context: ContextTypes.DEFAULT_TYPE) -> bool:
    user_id = update.effective_user.id
    chat_id = update.effective_chat.id
    member = await context.bot.get_chat_member(chat_id, user_id)
    return member.status in ['administrator', 'creator']

# Command handler for /ban (must be replied to a user's message)
async def ban_user(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    if not await is_admin(update, context):
        await update.message.reply_text("فقط مدیران و مالک گروه می‌توانند از این دستور استفاده کنند.")
        return

    if update.message.reply_to_message:
        user_to_ban = update.message.reply_to_message.from_user.id
        chat_id = update.effective_chat.id
        try:
            await context.bot.ban_chat_member(chat_id, user_to_ban)
            await update.message.reply_text(f"کاربر با آیدی {user_to_ban} بن شد.")
        except Exception as e:
            logger.error(f"Error banning user: {e}")
            await update.message.reply_text("خطایی در بن کردن کاربر رخ داد.")
    else:
        await update.message.reply_text("این دستور باید روی پیام کاربر ریپلای شود.")

# Command handler for /slot (slot machine game)
async def slot_machine(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    if not await is_admin(update, context):
        await update.message.reply_text("فقط مدیران و مالک گروه می‌توانند از این دستور استفاده کنند.")
        return

    await update.message.reply_dice(emoji=Dice.SLOT_MACHINE)

# Command handler for /meo (meow sound)
async def meo(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    if not await is_admin(update, context):
        await update.message.reply_text("فقط مدیران و مالک گروه می‌توانند از این دستور استفاده کنند.")
        return

    await update.message.reply_text("میوووووووووووووووووووووو")

# Command handler for /tas (dice roll)
async def tas(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    if not await is_admin(update, context):
        await update.message.reply_text("فقط مدیران و مالک گروه می‌توانند از این دستور استفاده کنند.")
        return

    await update.message.reply_dice(emoji=Dice.DICE)

def main() -> None:
    # Build the application
    application = ApplicationBuilder().token(BOT_TOKEN).build()

    # Add handlers
    application.add_handler(CommandHandler("ban", ban_user))
    application.add_handler(CommandHandler("slot", slot_machine))
    application.add_handler(CommandHandler("meo", meo))
    application.add_handler(CommandHandler("tas", tas))

    # Run the bot with polling (suitable for VPS; for webhook, see docs)
    application.run_polling()

if __name__ == '__main__':
    main()