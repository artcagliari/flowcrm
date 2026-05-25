import { motion } from 'framer-motion';
import { clsx } from 'clsx';

export default function Card({ className, children }) {
  return <motion.section initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} whileHover={{ y: -2 }} className={clsx('glass rounded-[24px] p-5', className)}>{children}</motion.section>;
}
