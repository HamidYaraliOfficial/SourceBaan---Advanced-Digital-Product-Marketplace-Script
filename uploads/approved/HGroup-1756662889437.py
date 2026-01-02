import asyncio
from datetime import datetime
from telethon import TelegramClient as xgv
from telethon.sessions import StringSession as ss
from telethon.tl.functions.channels import CreateChannelRequest as ccr
from telethon.tl.functions.channels import LeaveChannelRequest as lcr
#@n_5_3
class Hemo:
    def __init__(self):
        self.x: xgv = None
        self.apd: int = 12458477 #api id
        self.aph: str = "462356114bebf347f741078aede02d91 " #api Hash 
        self.ses: str = "سیشن اکانت"
        self.Gnmbr: int = 50 #تعداد گروه در روز میتونید 50 بسازه
        self.tit: str = "Hemo Group"
        self.Bio: str = "Powered by Hemo > @n_5_3"
        self.done: int = 0
        self.fshL: int = 0

    async def _init(self):
        self.x = xgv(ss(self.ses), self.apd, self.aph)
        await self.x.start()

    async def _Core(self, chat_id):
        for _ in range(7):
            try:
                await self.x.send_message(chat_id, ".")
                await asyncio.sleep(20)
            except Exception:
                pass
#@n_5_3
    async def _new(self, i: int) -> float:
        s = asyncio.get_event_loop().time()
        try:
            d = datetime.now().strftime("%Y-%m-%d")
            r = await self.x(ccr(
                title=f"{self.tit} {d} #{i+1}",
                about=self.Bio,
                megagroup=True
            ))
            chat_id = r.chats[0].id
            self.done += 1

            await self._Core(chat_id)

            await self.x(lcr(chat_id))
            return (asyncio.get_event_loop().time() - s) * 1000
        except Exception:
            self.fshL += 1
            return 0

    async def _run(self):
        t = []
        is_now = datetime.now().strftime("%Y/%m/%d")
        clr = {'w': '\033[0m', 'g': '\033[92m', 'r': '\033[91m', 'x': '\033[0m'}

        print(f"{is_now} > Need {clr['w']}{self.Gnmbr}{clr['x']} | Done {clr['g']}{self.done}{clr['x']} | Failed {clr['r']}{self.fshL}{clr['x']}", end="\r", flush=True)

        for i in range(self.Gnmbr):
            e = await self._new(i)
            t.append(e)
            is_now = datetime.now().strftime("%Y/%m/%d")
            print(f"{is_now} > Need {clr['w']}{self.Gnmbr}{clr['x']} | Done {clr['g']}{self.done}{clr['x']} | Failed {clr['r']}{self.fshL}{clr['x']}", end="\r", flush=True)
            await asyncio.sleep(1)
        print()

    async def start(self):
        try:
            await self._init()
            while True:
                await self._run()
                await asyncio.sleep(86400)
                self.done = self.fshL = 0
        except Exception as e:
            print(f"\n✘ E > {e}")
        finally:
            if self.x:
                await self.x.disconnect()

if __name__ == "__main__":
    lm5 = Hemo()
    asyncio.run(lm5.start())