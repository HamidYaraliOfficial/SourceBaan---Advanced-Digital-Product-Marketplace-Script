# Telegram Slot and Dice Bot
# Features:
# - /slot: Sends a random slot machine animation (using Telegram's dice feature)
# - /tas: Sends a random dice roll (using Telegram's dice feature)
# Accessible to all group members
# Designed for VPS deployment: Uses polling (simple); webhook for production
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

# Command handler for /slot (slot machine game)
async def slot_machine(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    await update.message.reply_dice(emoji=Dice.SLOT_MACHINE)

# Command handler for /tas (dice roll)
async def tas(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    await update.message.reply_dice(emoji=Dice.DICE)

def main() -> None:
    # Build the application
    application = ApplicationBuilder().token(BOT_TOKEN).build()

    # Add handlers
    application.add_handler(CommandHandler("slot", slot_machine))
    application.add_handler(CommandHandler("tas", tas))

    # Run the bot with polling (suitable for VPS; for webhook, see docs)
    application.run_polling()

if __name__ == '__main__':
    main()